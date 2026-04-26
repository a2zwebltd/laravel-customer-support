<?php

use A2ZWeb\CustomerSupport\Actions\ChangeTicketStatus;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Mail\TicketResolvedMail;
use A2ZWeb\CustomerSupport\Mail\TicketStatusChangedMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('sets resolved_at when moved to Resolved and emails the customer', function () {
    Mail::fake();
    $customer = User::factory()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $customer->id]);

    app(ChangeTicketStatus::class)->execute($ticket, TicketStatus::Resolved);

    $ticket->refresh();

    expect($ticket->status)->toBe(TicketStatus::Resolved)
        ->and($ticket->resolved_at)->not->toBeNull();

    Mail::assertQueued(TicketResolvedMail::class, fn ($m) => $m->hasTo($customer->email));
});

it('sends a generic status-change mail for non-resolved transitions', function () {
    Mail::fake();
    $customer = User::factory()->create();
    $ticket = SupportTicket::factory()->create([
        'user_id' => $customer->id,
        'status' => TicketStatus::Open->value,
    ]);

    app(ChangeTicketStatus::class)->execute($ticket, TicketStatus::InProgress);

    Mail::assertQueued(TicketStatusChangedMail::class, fn ($m) => $m->hasTo($customer->email)
        && $m->current === TicketStatus::InProgress);
});
