<?php

namespace A2ZWeb\CustomerSupport\Events;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;

class TicketAssigned
{
    use Dispatchable;

    public function __construct(
        public SupportTicket $ticket,
        public ?int $assigneeId,
        public ?int $previousAssigneeId,
    ) {}
}
