<?php

namespace A2ZWeb\CustomerSupport\Events;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;

class TicketCreated
{
    use Dispatchable;

    public function __construct(public SupportTicket $ticket) {}
}
