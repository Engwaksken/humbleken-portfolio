<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'summary' => fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
            'technologies' => fake()->randomElements(['Laravel', 'PostgreSQL', 'Networking', 'Linux', 'Docker'], 2),
            'status' => ProjectStatus::Draft,
            'published_at' => null,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ProjectStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => ProjectStatus::Published,
            'published_at' => now()->addWeek(),
        ]);
    }
}
