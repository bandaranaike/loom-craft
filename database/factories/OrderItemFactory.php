<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
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
            'product_id' => Product::factory(),
            'vendor_id' => Vendor::factory(),
            'quantity' => 1,
            'unit_price' => '180.00',
            'commission_rate' => '100.00',
            'commission_amount' => '180.00',
            'line_total' => '180.00',
        ];
    }
}
