<?php

namespace A2ZWeb\CustomerSupport\Console\Commands;

use A2ZWeb\CustomerSupport\Mail\TicketOverdueMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EscalateOverdueTickets extends Command
{
    protected $signature = 'support:escalate-overdue';

    protected $description = 'Notify admins about open support tickets past their SLA — once per overdue period, re-armed by ticket activity.';

    public function handle(): int
    {
        if (! config('customer-support.mail.notifications.ticket_overdue', true)) {
            $this->info('Overdue notifications are disabled.');

            return self::SUCCESS;
        }

        $admins = (array) config('customer-support.mail.admin_recipients', []);
        if (empty($admins)) {
            $this->warn('No support.admin_recipients configured — nothing to escalate to.');

            return self::SUCCESS;
        }

        $tickets = SupportTicket::query()->needsOverdueNotification()->get();

        if ($tickets->isEmpty()) {
            $this->info('No overdue tickets to notify about.');

            return self::SUCCESS;
        }

        foreach ($tickets as $ticket) {
            $this->line(' - '.$ticket->ticket_number.' overdue since '.$ticket->due_at?->diffForHumans());
            Mail::to($admins)->queue(new TicketOverdueMail($ticket));

            $ticket->overdue_notified_at = now();
            $ticket->saveQuietly();
        }

        $this->info('Notified admins about '.$tickets->count().' overdue ticket(s).');

        return self::SUCCESS;
    }
}
