<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Ticket;
use App\Models\User;

class TicketReopenedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $reopenedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, User $reopenedBy)
    {
        $this->ticket = $ticket;
        $this->reopenedBy = $reopenedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔓 Ticket Reabierto',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tickets.reopened',
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
        return $this->subject('🔓 Ticket Reabierto')
                    ->markdown('emails.tickets.reopened')
                    ->with([
                        'ticket' => $this->ticket,
                        'reopenedBy' => $this->reopenedBy,
                    ]);
    }
}
