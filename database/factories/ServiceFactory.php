<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_category_id' => null,
            'name' => fake()->words(4, true),
            'slug' => fake()->unique()->slug(),
            'summary' => fake()->optional()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'delivery_method' => fake()->optional()->randomElement(['Remote', 'On-site', 'Hybrid']),
            'estimated_duration' => fake()->optional()->randomElement(['1 hour', '1 day', '3 days', '1 week']),
            'indicative_price' => fake()->optional()->randomFloat(2, 50, 5000),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
