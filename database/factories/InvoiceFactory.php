<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->afterCreating(function (Order $order): void {
                $order->invoice()->delete();
            }),
            'status' => 'issued',
            'currency' => 'LKR',
            'subtotal' => '180.00',
            'commission_total' => '180.00',
            'total' => '180.00',
            'issued_at' => now(),
        ];
    }
}
