<?php

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an agent view any ticket and a customer only their own', function () {
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $agent = User::factory()->admin()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $customer->id]);

    expect($customer->can('view', $ticket))->toBeTrue()
        ->and($other->can('view', $ticket))->toBeFalse()
        ->and($agent->can('view', $ticket))->toBeTrue();
});

it('only lets agents change status, assign, and add internal notes', function () {
    $customer = User::factory()->create();
    $agent = User::factory()->admin()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $customer->id]);

    expect($customer->can('changeStatus', $ticket))->toBeFalse()
        ->and($customer->can('assign', $ticket))->toBeFalse()
        ->and($customer->can('addInternalNote', $ticket))->toBeFalse()
        ->and($agent->can('changeStatus', $ticket))->toBeTrue()
        ->and($agent->can('assign', $ticket))->toBeTrue()
        ->and($agent->can('addInternalNote', $ticket))->toBeTrue();
});
