<?php

use A2ZWeb\CustomerSupport\Actions\CreateTicket;
use A2ZWeb\CustomerSupport\DataTransferObjects\TicketData;
use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Mail\TicketCreatedMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('creates a ticket with a generated ticket number, default status and SLA due date', function () {
    Mail::fake();
    $user = User::factory()->create();

    $ticket = app(CreateTicket::class)->execute(new TicketData(
        userId: $user->id,
        subject: 'My audit failed to generate',
        description: 'When I try to run an audit it just hangs forever.',
        category: TicketCategory::Bug,
        priority: TicketPriority::High,
    ));

    expect($ticket)->toBeInstanceOf(SupportTicket::class)
        ->and($ticket->ticket_number)->toStartWith('CS-')
        ->and($ticket->status)->toBe(TicketStatus::Open)
        ->and($ticket->priority)->toBe(TicketPriority::High)
        ->and($ticket->due_at)->not->toBeNull()
        ->and($ticket->due_at->greaterThan(now()))->toBeTrue();

    expect($ticket->messages()->count())->toBe(1);
});

it('attaches uploaded files to the first message (single consume)', function () {
    Mail::fake();
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('screenshot.png', 50, 'image/png');

    $ticket = app(CreateTicket::class)->execute(new TicketData(
        userId: $user->id,
        subject: 'Bug with attachment',
        description: 'See screenshot.',
        category: TicketCategory::Bug,
        attachments: [$file],
    ));

    $message = $ticket->messages()->first();

    expect($message->getMedia('attachments')->count())->toBe(1)
        ->and($message->getMedia('attachments')->first()->file_name)->toBe('screenshot.png')
        ->and($ticket->getMedia('attachments')->count())->toBe(0);
});

it('emails the customer and admins on creation', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'cust@example.com']);

    app(CreateTicket::class)->execute(new TicketData(
        userId: $user->id,
        subject: 'Help',
        description: 'Need assistance with billing.',
        category: TicketCategory::Billing,
    ));

    Mail::assertQueued(TicketCreatedMail::class, fn ($m) => $m->hasTo('cust@example.com') && $m->forAgent === false);
    Mail::assertQueued(TicketCreatedMail::class, fn ($m) => $m->hasTo('agents@example.com') && $m->forAgent === true);
});
