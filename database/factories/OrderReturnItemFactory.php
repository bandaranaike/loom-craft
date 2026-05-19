<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderReturnItem>
 */
class OrderReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_return_id' => OrderReturn::factory(),
            'order_item_id' => OrderItem::factory(),
            'quantity' => 1,
            'condition' => null,
            'resolution' => null,
            'note' => null,
        ];
    }
}
