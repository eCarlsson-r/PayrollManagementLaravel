<?php

namespace App\Http\Controllers;

use App\Events\FlagDecisionUpdated;
use App\Models\PayrollFlag;
use App\Models\WorkflowMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Human-in-the-loop workflow status page: live Band chat log + approve/reject
 * of flagged payroll entries (which unblocks Agent 4).
 */
class WorkflowController extends Controller
{
    /** GET /workflow */
    public function index(Request $request)
    {
        // Periods that have any activity, newest first.
        $periods = WorkflowMessage::query()
            ->whereNotNull('period')
            ->select('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period');

        $period = $request->query('period') ?: $periods->first();

        $messages = WorkflowMessage::where('period', $period)
            ->orderBy('id')
            ->get();

        $flags = PayrollFlag::where('period', $period)
            ->orderBy('id')
            ->get();

        return view('Workflow', [
            'period' => $period,
            'periods' => $periods,
            'messages' => $messages,
            'flags' => $flags,
            'status' => $this->runStatus($messages, $flags),
        ]);
    }

    /** POST /workflow/flag/{id} — approve or reject a flagged entry, with optional net-amount correction. */
    public function resolveFlag(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'corrected_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $flag = PayrollFlag::findOrFail($id);
        $flag->decision = $validated['decision'];
        $flag->resolved = true;
        $flag->resolved_at = now();

        if (isset($validated['corrected_amount'])) {
            $data = $flag->data ?? [];
            $data['corrected_amount'] = (int) round($validated['corrected_amount']);
            $flag->data = $data;
        }

        $flag->save();

        broadcast(new FlagDecisionUpdated($flag));

        return response()->json([
            'success' => true,
            'id' => $flag->id,
            'decision' => $flag->decision,
            'corrected_amount' => $flag->data['corrected_amount'] ?? null,
        ]);
    }

    /** Derive a run status for the status badge. */
    private function runStatus($messages, $flags): string
    {
        if ($messages->isEmpty() && $flags->isEmpty()) {
            return 'idle';
        }
        if ($flags->where('decision', 'pending')->isNotEmpty()) {
            return 'awaiting_approval';
        }
        $hasReport = $messages->contains(
            fn ($m) => $m->message_type === 'report' || $m->agent_name === 'Report Generator'
        );

        return $hasReport ? 'completed' : 'running';
    }
}
