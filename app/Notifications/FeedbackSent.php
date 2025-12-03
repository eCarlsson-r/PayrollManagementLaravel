<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\Employee;

class FeedbackSent extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($feedback)
    {
        $this->feedback = $feedback;
        $employee = Employee::find($feedback->employee_id);
        $this->feedback->name = $employee->first_name . ' ' . $employee->last_name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'id' => $this->feedback->id,
            'employee_name' => $this->feedback->name,
            'title' => $this->feedback->title
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'id' => $this->feedback->id,
            'employee_name' => $this->feedback->name,
            'title' => $this->feedback->title
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->feedback->title)
            ->body($this->feedback->name)
            ->action('View Feedback', '/feedback/'.$this->feedback->id)
            ->data([
                'id' => $this->feedback->id,
                'employee_name' => $this->feedback->name,
                'title' => $this->feedback->title
            ])
            // ->badge()
            // ->dir()
            // ->image()
            // ->lang()
            // ->renotify()
            // ->requireInteraction()
            // ->tag()
            // ->vibrate()
            ->options(['TTL' => 1000]);
    }
}