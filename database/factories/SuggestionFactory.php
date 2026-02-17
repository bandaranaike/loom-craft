<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Suggestion>
 */
class SuggestionFactory extends Factory
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
            'guest_email' => null,
            'title' => fake()->sentence(4),
            'details' => fake()->paragraph(),
            'status' => 'pending',
            'handled_by' => null,
        ];
    }
}
