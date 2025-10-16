<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * MessageSent Event
 * 
 * This event is triggered when a new message is sent and needs to be broadcast
 * to connected clients via WebSocket for real-time messaging functionality.
 * 
 * @package App\Events
 * @author Laravel Application
 * @version 1.0.0
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message instance that was sent
     * 
     * @var Message
     */
    public $message;

    /**
     * Create a new event instance
     * 
     * @param Message $message The message that was sent
     * @throws Exception If message validation fails
     */
    public function __construct(Message $message)
    {
        try {
            // Validate message data
            if (!$message || !$message->id) {
                throw new Exception('Invalid message data provided');
            }

            // Sanitize and store the message
            $this->message = $message;
            
            // Log the event for debugging
            Log::info('MessageSent event created', [
                'message_id' => $message->id,
                'user_id' => $message->user_id ?? null,
                'timestamp' => now()
            ]);
            
        } catch (Exception $e) {
            Log::error('Error creating MessageSent event: ' . $e->getMessage(), [
                'message_data' => $message ? $message->toArray() : null
            ]);
            throw $e;
        }
    }

    /**
     * Get the channels the event should broadcast on
     * 
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        try {
            return new Channel('message');
        } catch (Exception $e) {
            Log::error('Error getting broadcast channels: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the data to broadcast
     * 
     * @return array
     */
    public function broadcastWith()
    {
        try {
            return [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'user_id' => $this->message->user_id,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at
            ];
        } catch (Exception $e) {
            Log::error('Error preparing broadcast data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * The event's broadcast name
     * 
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }
}
