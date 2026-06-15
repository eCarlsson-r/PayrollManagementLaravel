<?php

namespace App\Events;

use App\Models\PayrollFlag;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when Agent 3 raises a new flag, so an open /workflow page shows the
 * approve/reject card live (without a refresh).
 */
class FlagCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PayrollFlag $flag) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('workflow')];
    }

    public function broadcastAs(): string
    {
        return 'workflow.flag-created';
    }

    public function broadcastWith(): array
    {
        $employee = $this->flag->employee;

        return [
            'id' => $this->flag->id,
            'employee_id' => $this->flag->employee_id,
            'employee_name' => $employee
                ? $employee->first_name . ' ' . $employee->last_name
                : ('Employee #' . $this->flag->employee_id),
            'period' => $this->flag->period,
            'reason' => $this->flag->reason,
            'severity' => $this->flag->severity,
            'gross_amount' => $this->flag->gross_amount,
            'net_amount' => $this->flag->net_amount,
            'decision' => $this->flag->decision,
            'explanation' => $this->flag->data['explanation'] ?? null,
        ];
    }
}
