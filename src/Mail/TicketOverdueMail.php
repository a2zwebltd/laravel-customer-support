<?php

namespace A2ZWeb\CustomerSupport\Mail;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketOverdueMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->ticket->ticket_number}] Overdue — needs attention",
            from: $this->resolveFrom(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'customer-support::mail.ticket-overdue',
            with: [
                'ticket' => $this->ticket,
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
