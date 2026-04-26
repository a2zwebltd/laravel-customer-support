<?php

namespace A2ZWeb\CustomerSupport\Database\Factories;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketMessage>
 */
class SupportTicketMessageFactory extends Factory
{
    protected $model = SupportTicketMessage::class;

    public function definition(): array
    {
        $userModel = config('customer-support.user_model')
            ?? config('auth.providers.'.config('auth.guards.web.provider', 'users').'.model', User::class);

        return [
            'ticket_id' => SupportTicket::factory(),
            'user_id' => $userModel::factory(),
            'body' => $this->faker->paragraph(),
            'is_internal_note' => false,
        ];
    }

    public function internalNote(): self
    {
        return $this->state(['is_internal_note' => true]);
    }
}
