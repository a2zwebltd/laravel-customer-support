<?php

use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;

it('reports correct open/closed status helpers', function () {
    expect(TicketStatus::Open->isOpen())->toBeTrue()
        ->and(TicketStatus::AwaitingCustomer->isOpen())->toBeTrue()
        ->and(TicketStatus::Resolved->isClosed())->toBeTrue()
        ->and(TicketStatus::Closed->isClosed())->toBeTrue();
});

it('returns SLA hours per priority from config', function () {
    expect(TicketPriority::Urgent->slaHours())->toBe(4)
        ->and(TicketPriority::High->slaHours())->toBe(12)
        ->and(TicketPriority::Normal->slaHours())->toBe(48)
        ->and(TicketPriority::Low->slaHours())->toBe(120);
});
