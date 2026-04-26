<?php

use A2ZWeb\CustomerSupport\Actions\ReplyToTicket;
use A2ZWeb\CustomerSupport\DataTransferObjects\MessageData;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Mail\TicketRepliedMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('records first response timestamp when an agent replies', function () {
    Mail::fake();
    $customer = User::factory()->create();
    $agent = User::factory()->admin()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $customer->id]);

    expect($ticket->first_response_at)->toBeNull();

    app(ReplyToTicket::class)->execute(new MessageData(
        ticketId: $ticket->id,
        userId: $agent->id,
        body: 'Looking into this now.',
    ));

    $ticket->refresh();

    expect($ticket->first_response_at)->not->toBeNull()
        ->and($ticket->status)->toBe(TicketStatus::AwaitingCustomer);

    Mail::assertQueued(TicketRepliedMail::class, fn ($m) => $m->hasTo($customer->email) && $m->forCustomer === true);
});

it('marks ticket as Pending when customer replies', function () {
    Mail::fake();
    $customer = User::factory()->create();
    $ticket = SupportTicket::factory()->create([
        'user_id' => $customer->id,
        'status' => TicketStatus::AwaitingCustomer->value,
    ]);

    app(ReplyToTicket::class)->execute(new MessageData(
        ticketId: $ticket->id,
        userId: $customer->id,
        body: 'Still happening.',
    ));

    expect($ticket->refresh()->status)->toBe(TicketStatus::Pending);
});

it('does not email anyone for an internal note', function () {
    Mail::fake();
    $customer = User::factory()->create();
    $agent = User::factory()->admin()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $customer->id]);

    app(ReplyToTicket::class)->execute(new MessageData(
        ticketId: $ticket->id,
        userId: $agent->id,
        body: 'Internal: customer is on the Pro plan.',
        isInternalNote: true,
    ));

    Mail::assertNothingQueued();

    expect($ticket->refresh()->messages()->where('is_internal_note', true)->count())->toBe(1);
});
