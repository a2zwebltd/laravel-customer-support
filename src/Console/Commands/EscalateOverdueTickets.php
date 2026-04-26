<?php

namespace A2ZWeb\CustomerSupport\Console\Commands;

use A2ZWeb\CustomerSupport\Mail\TicketStatusChangedMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EscalateOverdueTickets extends Command
{
    protected $signature = 'support:escalate-overdue';

    protected $description = 'Find open support tickets past their SLA due date and notify admins.';

    public function handle(): int
    {
        $admins = (array) config('customer-support.mail.admin_recipients', []);
        if (empty($admins)) {
            $this->warn('No support.admin_recipients configured — nothing to escalate to.');

            return self::SUCCESS;
        }

        $tickets = SupportTicket::query()->overdue()->get();

        if ($tickets->isEmpty()) {
            $this->info('No overdue tickets.');

            return self::SUCCESS;
        }

        foreach ($tickets as $ticket) {
            $this->line(' - '.$ticket->ticket_number.' overdue since '.$ticket->due_at?->diffForHumans());
            Mail::to($admins)->queue(new TicketStatusChangedMail($ticket, $ticket->status, $ticket->status));
        }

        $this->info('Notified admins about '.$tickets->count().' overdue ticket(s).');

        return self::SUCCESS;
    }
}
