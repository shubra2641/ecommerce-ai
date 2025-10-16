<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Status Notification Class
 * 
 * Handles status change notifications for orders and other entities
 * Supports database and broadcast channels
 */
class StatusNotification extends Notification
{
    use Queueable;

    /**
     * Notification details
     * 
     * @var array
     */
    private $details;

    /**
     * Create a new notification instance.
     *
     * @param array $details Notification details
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->details['title'],
            'actionURL' => $this->details['actionURL'],
            'fas' => $this->details['fas']
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->details['title'],
            'actionURL' => $this->details['actionURL'],
            'url' => route('admin.notification', $this->id),
            'fas' => $this->details['fas'],
            'time' => date('F d, Y h:i A')
        ]);
    }
}
