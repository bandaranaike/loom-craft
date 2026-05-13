<?php

namespace Database\Factories;

use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingService>
 */
class ShippingServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_carrier_id' => ShippingCarrier::factory(),
            'name' => fake()->unique()->randomElement(['Standard', 'Express', 'Economy']).' '.fake()->numberBetween(1, 999),
            'code' => fake()->unique()->bothify('SVC-###'),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
