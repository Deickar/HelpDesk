<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id ?? 1,
            'assigned_to' => \App\Models\User::inRandomOrder()->first()->id ?? 1,
            'subject' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status_id' => \App\Models\Status::inRandomOrder()->first()->id ?? 1,
            'priority_id' => \App\Models\Priority::inRandomOrder()->first()->id ?? 1,
            'category_id' => \App\Models\Category::inRandomOrder()->first()->id ?? 1,
            'closed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
