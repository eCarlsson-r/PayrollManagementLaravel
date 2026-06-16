<?php

namespace App\Http\Controllers\Api;

use App\Events\FlagCreated;
use App\Events\WorkflowMessageCreated;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\PayrollFlag;
use App\Models\Payslip;
use App\Models\WorkflowMessage;
use App\Services\PaymentCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * JSON API consumed by the Band agent pipeline.
 *
 * Pipeline: Agent 1 (data collection) -> calculate, Agent 2 (tax/BPJS),
 * Agent 3 (compliance) -> flag, Agent 4 (reports) -> submit.
 */
class PayrollApiController extends Controller
{
    /**
     * GET /api/payroll/calculate
     *
     * Wraps PaymentCalculator and returns the computed payroll rows as JSON.
     * Agent 1 calls this to seed the pipeline.
     */
    public function calculate(Request $request, PaymentCalculator $calculator): JsonResponse
    {
        $date = $request->query('date'); // optional Y-m-d, drives MONTHLY/COMMISSION month-end logic

        if ($date !== null && strtotime($date) === false) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date. Expected format Y-m-d.',
            ], 422);
        }

        $rows = $calculator->calculatePayments($date);

        return response()->json([
            'success' => true,
            'date' => $date ?? date('Y-m-d'),
            'count' => count($rows),
            'payments' => $rows,
        ]);
    }

    /**
     * POST /api/payroll/submit
     *
     * Persists finalized payroll rows as Payment records. Mirrors
     * PaymentController@store but validates input and returns JSON.
     * Agent 4 calls this after compliance review passes.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.employee_id' => ['required'],
            'payments.*.amount' => ['required', 'numeric'],
            'payments.*.method' => ['nullable', 'string'],
            'payments.*.date' => ['nullable', 'date'],
        ]);

        $created = [];
        $errors = [];

        foreach ($validated['payments'] as $index => $pymt) {
            $employee = Employee::find($pymt['employee_id']);

            if ($employee === null) {
                $errors[] = [
                    'index' => $index,
                    'employee_id' => $pymt['employee_id'],
                    'error' => 'Employee not found.',
                ];
                continue;
            }

            $created[] = Payment::create([
                'employee_id' => $employee->id,
                'date' => $pymt['date'] ?? date('Y-m-d'),
                'amount' => (int) round($pymt['amount']),
                'method' => $pymt['method'] ?? $employee->pay_method,
            ]);
        }

        return response()->json([
            'success' => $errors === [],
            'created_count' => count($created),
            'payments' => $created,
            'errors' => $errors,
        ], $created === [] ? 422 : 201);
    }

    /**
     * POST /api/payroll/flag
     *
     * Records payroll entries flagged for human review. Accepts either a
     * single flag object or a batch via { "flags": [ ... ] }.
     * Agent 3 calls this when it detects an anomaly or compliance breach.
     */
    public function flag(Request $request): JsonResponse
    {
        $items = $request->has('flags')
            ? $request->input('flags')
            : [$request->except('flags')];

        if (! is_array($items) || $items === []) {
            return response()->json([
                'success' => false,
                'message' => 'No flag data provided.',
            ], 422);
        }

        $created = [];
        $errors = [];

        foreach ($items as $index => $item) {
            $validator = Validator::make(is_array($item) ? $item : [], [
                'reason' => ['required', 'string'],
                'employee_id' => ['nullable'],
                'period' => ['nullable', 'string'],
                'severity' => ['nullable', 'string'],
                'gross_amount' => ['nullable', 'numeric'],
                'net_amount' => ['nullable', 'numeric'],
                'data' => ['nullable', 'array'],
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'index' => $index,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            $valid = $validator->validated();

            // Upsert so pipeline re-runs for the same employee+period reset the
            // flag to pending instead of stacking duplicate rows that Agent 4
            // would poll on indefinitely.
            $flag = PayrollFlag::updateOrCreate(
                [
                    'employee_id' => $valid['employee_id'] ?? null,
                    'period' => $valid['period'] ?? null,
                ],
                [
                    'reason' => $valid['reason'],
                    'severity' => $valid['severity'] ?? 'warning',
                    'gross_amount' => isset($valid['gross_amount']) ? (int) round($valid['gross_amount']) : null,
                    'net_amount' => isset($valid['net_amount']) ? (int) round($valid['net_amount']) : null,
                    'data' => $valid['data'] ?? null,
                    'resolved' => false,
                    'decision' => 'pending',
                    'resolved_at' => null,
                ]
            );

            broadcast(new FlagCreated($flag)); // show the card live on /workflow
            $created[] = $flag;
        }

        return response()->json([
            'success' => $errors === [],
            'flagged_count' => count($created),
            'flags' => $created,
            'errors' => $errors,
        ], $created === [] ? 422 : 201);
    }

    /**
     * POST /api/payroll/log
     *
     * Records a chat message an agent posted into the Band room, so the
     * /workflow page can visualize the conversation. Broadcasts live.
     */
    public function log(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string'],
            'agent' => ['nullable', 'string'],
            'sender_type' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'content' => ['required', 'string'],
        ]);

        $message = WorkflowMessage::create([
            'period' => $validated['period'] ?? null,
            'agent_name' => $validated['agent'] ?? null,
            'sender_type' => $validated['sender_type'] ?? 'Agent',
            'message_type' => $validated['type'] ?? 'message',
            'content' => $validated['content'],
        ]);

        broadcast(new WorkflowMessageCreated($message));

        return response()->json(['success' => true, 'id' => $message->id], 201);
    }

    /**
     * POST /api/payroll/payslip
     *
     * Generates a PDF payslip per employee from the data Agent 4 sends after
     * submitting payments. PDFs are stored in storage/app/payslips/ and a
     * Payslip record is upserted (safe to call on pipeline re-runs).
     */
    public function payslip(Request $request): JsonResponse
    {
        $items = $request->has('payslips')
            ? $request->input('payslips')
            : [$request->except('payslips')];

        $created = [];
        $errors  = [];

        foreach ($items as $index => $item) {
            $validator = Validator::make(is_array($item) ? $item : [], [
                'employee_id'      => ['required'],
                'period'           => ['required', 'string'],
                'gross'            => ['required', 'numeric'],
                'net'              => ['required', 'numeric'],
                'pph21'            => ['nullable', 'numeric'],
                'bpjs_total'       => ['nullable', 'numeric'],
                'corrected_amount' => ['nullable', 'numeric'],
                'date'             => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                $errors[] = ['index' => $index, 'errors' => $validator->errors()->all()];
                continue;
            }

            $v = $validator->validated();
            $employee = Employee::find($v['employee_id']);

            if ($employee === null) {
                $errors[] = ['index' => $index, 'employee_id' => $v['employee_id'], 'error' => 'Employee not found.'];
                continue;
            }

            $path = "payslips/{$v['period']}/employee_{$employee->id}.pdf";

            $payslip = Payslip::updateOrCreate(
                ['employee_id' => $employee->id, 'period' => $v['period']],
                [
                    'gross_amount'     => (int) round($v['gross']),
                    'pph21'            => (int) round($v['pph21'] ?? 0),
                    'bpjs_total'       => (int) round($v['bpjs_total'] ?? 0),
                    'net_amount'       => (int) round($v['net']),
                    'corrected_amount' => isset($v['corrected_amount']) ? (int) round($v['corrected_amount']) : null,
                    'file_path'        => $path,
                    'data'             => $v,
                ]
            );

            $pdf = Pdf::loadView('payslip_pdf', compact('payslip', 'employee'));
            Storage::put($path, $pdf->output());

            $created[] = ['id' => $payslip->id, 'employee_id' => $employee->id, 'period' => $v['period']];
        }

        return response()->json([
            'success'       => $errors === [],
            'created_count' => count($created),
            'payslips'      => $created,
            'errors'        => $errors,
        ], $created === [] ? 422 : 201);
    }

    /**
     * GET /api/payroll/flags?period=YYYY-MM
     *
     * Returns flags (optionally for one period) with their approve/reject
     * decision. Agent 4 polls this to learn when flagged entries are resolved.
     */
    public function flags(Request $request): JsonResponse
    {
        $query = PayrollFlag::query();
        if ($period = $request->query('period')) {
            $query->where('period', $period);
        }

        $flags = $query->get(['id', 'employee_id', 'period', 'decision', 'resolved', 'data']);

        return response()->json([
            'success' => true,
            'count' => $flags->count(),
            'flags' => $flags->map(fn ($f) => [
                'id' => $f->id,
                'employee_id' => $f->employee_id,
                'period' => $f->period,
                'decision' => $f->decision,
                'resolved' => $f->resolved,
                'corrected_amount' => $f->data['corrected_amount'] ?? null,
            ]),
        ]);
    }
}
