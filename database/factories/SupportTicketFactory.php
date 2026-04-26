<?php

namespace A2ZWeb\CustomerSupport\Database\Factories;

use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        $userModel = config('customer-support.user_model')
            ?? config('auth.providers.'.config('auth.guards.web.provider', 'users').'.model', User::class);

        return [
            'user_id' => $userModel::factory(),
            'subject' => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(2, true),
            'category' => $this->faker->randomElement(TicketCategory::cases())->value,
            'priority' => TicketPriority::Normal->value,
            'status' => TicketStatus::Open->value,
        ];
    }

    public function open(): self
    {
        return $this->state(['status' => TicketStatus::Open->value]);
    }

    public function resolved(): self
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Resolved->value,
            'resolved_at' => now(),
        ]);
    }

    public function urgent(): self
    {
        return $this->state(['priority' => TicketPriority::Urgent->value]);
    }

    public function overdue(): self
    {
        return $this->state(['due_at' => now()->subHour()]);
    }

    public function unassigned(): self
    {
        return $this->state(['assigned_to' => null]);
    }
}
