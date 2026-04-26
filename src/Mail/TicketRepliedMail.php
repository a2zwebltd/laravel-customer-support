<?php

namespace A2ZWeb\CustomerSupport\Mail;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketRepliedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketMessage $message,
        public bool $forCustomer = true,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->forCustomer
            ? "Re: [{$this->ticket->ticket_number}] {$this->ticket->subject}"
            : "[Reply] {$this->ticket->ticket_number} — {$this->ticket->subject}";

        return new Envelope(
            subject: $subject,
            from: $this->resolveFrom(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'customer-support::mail.ticket-replied',
            with: [
                'ticket' => $this->ticket,
                'message' => $this->message,
                'forCustomer' => $this->forCustomer,
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
