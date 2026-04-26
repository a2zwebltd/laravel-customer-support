<?php

namespace A2ZWeb\CustomerSupport\Mail;

use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public TicketStatus $previous,
        public TicketStatus $current,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->ticket->ticket_number}] Status updated to {$this->current->label()}",
            from: $this->resolveFrom(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'customer-support::mail.ticket-status-changed',
            with: [
                'ticket' => $this->ticket,
                'previous' => $this->previous,
                'current' => $this->current,
            ],
        );
    }

    private function resolveFrom(): ?Address
    {
        $address = config('customer-support.mail.from.address');
        if (! $address) {
            return null;
        }

        return new Address(
            $address,
            (string) (config('customer-support.mail.from.name') ?? config('app.name'))
        );
    }
}
