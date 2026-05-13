<?php

namespace Database\Factories;

use App\Models\ShippingCarrier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingCarrier>
 */
class ShippingCarrierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Courier',
            'code' => fake()->unique()->bothify('CAR-###'),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
