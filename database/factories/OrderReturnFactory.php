<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderReturn>
 */
class OrderReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => 'requested',
            'reason' => 'damaged_item',
            'customer_note' => fake()->sentence(),
            'requested_at' => now(),
        ];
    }
}
