<?php

use A2ZWeb\CustomerSupport\Mail\TicketOverdueMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('notifies admins once when a ticket is overdue', function () {
    Mail::fake();
    $ticket = SupportTicket::factory()->overdue()->create();

    $this->artisan('support:escalate-overdue')->assertSuccessful();

    Mail::assertQueued(
        TicketOverdueMail::class,
        fn ($mail) => $mail->hasTo('agents@example.com') && $mail->ticket->is($ticket)
    );

    expect($ticket->fresh()->overdue_notified_at)->not->toBeNull();
});

it('does not notify again on the next run without ticket activity', function () {
    Mail::fake();
    SupportTicket::factory()->overdue()->create();

    $this->artisan('support:escalate-overdue')->assertSuccessful();
    $this->artisan('support:escalate-overdue')->assertSuccessful();

    Mail::assertQueued(TicketOverdueMail::class, 1);
});

it('re-notifies after the ticket sees new activity', function () {
    Mail::fake();
    $ticket = SupportTicket::factory()->overdue()->create();

    $this->artisan('support:escalate-overdue')->assertSuccessful();

    // Any later activity bumps last_activity_at past the notification time.
    $ticket->forceFill(['last_activity_at' => now()->addMinutes(5)])->saveQuietly();

    $this->artisan('support:escalate-overdue')->assertSuccessful();

    Mail::assertQueued(TicketOverdueMail::class, 2);
});

it('ignores tickets that are not overdue', function () {
    Mail::fake();
    SupportTicket::factory()->create(['due_at' => now()->addDay()]);

    $this->artisan('support:escalate-overdue')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('ignores resolved tickets even when due_at is in the past', function () {
    Mail::fake();
    SupportTicket::factory()->resolved()->create(['due_at' => now()->subDay()]);

    $this->artisan('support:escalate-overdue')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('does nothing when overdue notifications are disabled', function () {
    Mail::fake();
    config()->set('customer-support.mail.notifications.ticket_overdue', false);
    SupportTicket::factory()->overdue()->create();

    $this->artisan('support:escalate-overdue')->assertSuccessful();

    Mail::assertNothingQueued();
});
