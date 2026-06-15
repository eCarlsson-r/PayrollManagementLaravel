<?php

namespace App\Events;

use App\Models\PayrollFlag;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a flag's approve/reject decision changes, so every open
 * /workflow page updates the flag card live.
 */
class FlagDecisionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PayrollFlag $flag) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('workflow')];
    }

    public function broadcastAs(): string
    {
        return 'workflow.flag';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->flag->id,
            'employee_id' => $this->flag->employee_id,
            'decision' => $this->flag->decision,
            'resolved' => $this->flag->resolved,
            'period' => $this->flag->period,
            'corrected_amount' => $this->flag->data['corrected_amount'] ?? null,
        ];
    }
}
