<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'vendor_price' => fake()->randomFloat(2, 50, 5000),
            'commission_rate' => 7.00,
            'selling_price' => fake()->randomFloat(2, 60, 5500),
            'materials' => fake()->words(4, true),
            'pieces_count' => fake()->numberBetween(1, 10),
            'production_time_days' => fake()->numberBetween(7, 60),
            'dimension_length' => fake()->randomFloat(2, 10, 200),
            'dimension_width' => fake()->randomFloat(2, 10, 200),
            'dimension_height' => fake()->randomFloat(2, 1, 50),
            'dimension_unit' => 'cm',
            'status' => 'pending_review',
        ];
    }
}
