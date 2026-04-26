<?php

namespace A2ZWeb\CustomerSupport\Actions;

use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Events\TicketStatusChanged;
use A2ZWeb\CustomerSupport\Mail\TicketResolvedMail;
use A2ZWeb\CustomerSupport\Mail\TicketStatusChangedMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ChangeTicketStatus
{
    public function execute(SupportTicket $ticket, TicketStatus $status, ?int $changedByUserId = null): SupportTicket
    {
        $previous = $ticket->status;

        if ($previous === $status) {
            return $ticket;
        }

        DB::transaction(function () use ($ticket, $status, $previous, $changedByUserId): void {
            $ticket->status = $status;
            $ticket->last_activity_at = now();

            if ($status === TicketStatus::Resolved && ! $ticket->resolved_at) {
                $ticket->resolved_at = now();
            }

            if ($status === TicketStatus::Closed && ! $ticket->closed_at) {
                $ticket->closed_at = now();
            }

            if ($previous === TicketStatus::Resolved && $status->isOpen()) {
                $ticket->resolved_at = null;
            }
            if ($previous === TicketStatus::Closed && $status->isOpen()) {
                $ticket->closed_at = null;
            }

            $ticket->save();

            TicketStatusChanged::dispatch($ticket, $previous, $status, $changedByUserId);

            $this->sendNotifications($ticket, $previous, $status);
        });

        return $ticket->refresh();
    }

    private function sendNotifications(SupportTicket $ticket, TicketStatus $previous, TicketStatus $current): void
    {
        $customerEmail = optional($ticket->user)->email;
        if (! $customerEmail) {
            return;
        }

        if ($current === TicketStatus::Resolved && config('customer-support.mail.notifications.ticket_resolved', true)) {
            Mail::to($customerEmail)->queue(new TicketResolvedMail($ticket));

            return;
        }

        if (config('customer-support.mail.notifications.status_changed', true)) {
            Mail::to($customerEmail)->queue(new TicketStatusChangedMail($ticket, $previous, $current));
        }
    }
}
