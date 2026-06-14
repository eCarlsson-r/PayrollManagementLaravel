<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\PayrollFlag;
use App\Services\PaymentCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

            $created[] = PayrollFlag::create([
                'employee_id' => $valid['employee_id'] ?? null,
                'period' => $valid['period'] ?? null,
                'reason' => $valid['reason'],
                'severity' => $valid['severity'] ?? 'warning',
                'gross_amount' => isset($valid['gross_amount']) ? (int) round($valid['gross_amount']) : null,
                'net_amount' => isset($valid['net_amount']) ? (int) round($valid['net_amount']) : null,
                'data' => $valid['data'] ?? null,
                'resolved' => false,
            ]);
        }

        return response()->json([
            'success' => $errors === [],
            'flagged_count' => count($created),
            'flags' => $created,
            'errors' => $errors,
        ], $created === [] ? 422 : 201);
    }
}
