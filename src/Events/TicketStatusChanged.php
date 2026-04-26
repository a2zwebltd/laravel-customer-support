<?php

namespace A2ZWeb\CustomerSupport\Events;

use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;

class TicketStatusChanged
{
    use Dispatchable;

    public function __construct(
        public SupportTicket $ticket,
        public TicketStatus $previous,
        public TicketStatus $current,
        public ?int $changedByUserId = null,
    ) {}
}
