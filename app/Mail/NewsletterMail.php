<?php

namespace App\Mail;

use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Newsletter Mail Class
 * 
 * Handles sending newsletter emails to subscribers
 * with proper formatting and unsubscribe functionality.
 */
class NewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The newsletter subject
     *
     * @var string
     */
    public $subject;

    /**
     * The newsletter content
     *
     * @var string
     */
    public $content;

    /**
     * The subscriber information
     *
     * @var Newsletter
     */
    public $subscriber;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $content
     * @param Newsletter $subscriber
     */
    public function __construct($subject, $content, Newsletter $subscriber)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->subscriber = $subscriber;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'subject' => $this->subject,
                'content' => $this->content,
                'subscriber' => $this->subscriber,
                'unsubscribeUrl' => $this->subscriber->getUnsubscribeUrl(),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}