<?php

namespace A2ZWeb\CustomerSupport\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'is_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): self
    {
        return $this->state(['is_admin' => true]);
    }
}
