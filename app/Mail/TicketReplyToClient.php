<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

class TicketReplyToClient extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $message;
    public $responder;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, TicketMessage $message, User $responder)
    {
        $this->ticket = $ticket;
        $this->message = $message;
        $this->responder = $responder;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📩 Tienes una nueva respuesta a tu ticket',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tickets.reply_to_client',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('📩 Tienes una nueva respuesta a tu ticket')
                    ->markdown('emails.tickets.reply_to_client')
                    ->with([
                        'ticket' => $this->ticket,
                        'message' => $this->message,
                        'responder' => $this->responder,
                    ]);
    }
}
