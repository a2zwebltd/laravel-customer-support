<?php

namespace A2ZWeb\CustomerSupport\Actions;

use A2ZWeb\CustomerSupport\Events\TicketAssigned;
use A2ZWeb\CustomerSupport\Models\SupportTicket;

class AssignTicket
{
    public function execute(SupportTicket $ticket, ?int $assigneeId): SupportTicket
    {
        $previous = $ticket->assigned_to;

        if ($previous === $assigneeId) {
            return $ticket;
        }

        $ticket->assigned_to = $assigneeId;
        $ticket->last_activity_at = now();
        $ticket->save();

        TicketAssigned::dispatch($ticket, $assigneeId, $previous);

        return $ticket;
    }
}
