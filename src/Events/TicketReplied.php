<?php

namespace A2ZWeb\CustomerSupport\Events;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use Illuminate\Foundation\Events\Dispatchable;

class TicketReplied
{
    use Dispatchable;

    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketMessage $message,
    ) {}
}
