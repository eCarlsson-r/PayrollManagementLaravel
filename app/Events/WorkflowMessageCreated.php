<?php

namespace App\Events;

use App\Models\WorkflowMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when an agent posts a chat message into a workflow run.
 * Drives the live Band chat log on the /workflow page.
 *
 * ShouldBroadcastNow: broadcast synchronously so no queue worker is required.
 */
class WorkflowMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WorkflowMessage $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('workflow')];
    }

    public function broadcastAs(): string
    {
        return 'workflow.message';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'period' => $this->message->period,
            'agent_name' => $this->message->agent_name,
            'sender_type' => $this->message->sender_type,
            'message_type' => $this->message->message_type,
            'content' => $this->message->content,
            'created_at' => optional($this->message->created_at)->toIso8601String(),
        ];
    }
}
