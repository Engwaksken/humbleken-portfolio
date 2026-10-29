<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(5),
            'status' => TicketStatus::Open,
            'last_activity_at' => now(),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (): array => ['status' => TicketStatus::Closed]);
    }
}
