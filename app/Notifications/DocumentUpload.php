<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;    
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\Employee;

class DocumentUpload extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->document = $data;
        $employee = Employee::find($this->document->employee_id);
        $this->document->name = $employee->first_name . ' ' . $employee->last_name;
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
            'id' => $this->document->id,
            'employee_name' => $this->document->name,
            'title' => $this->document->subject
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'id' => $this->document->id,
            'employee_name' => $this->document->name,
            'title' => $this->document->subject
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->document->subject)
            ->body($this->document->name . ' at ' . $this->document->date)
            ->action('View Document', '/document/'.$this->document->id)
            ->data([
                'id' => $this->document->id,
                'employee_name' => $this->document->name,
                'title' => $this->document->subject
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
