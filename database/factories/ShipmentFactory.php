<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
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
            'vendor_id' => null,
            'responsibility' => 'platform',
            'status' => 'pending',
            'carrier' => fake()->randomElement(['DHL eCommerce', 'UPS', 'FedEx']),
            'service_level' => 'Standard',
            'tracking_number' => fake()->numerify('##########'),
            'package_count' => 1,
            'parcel_weight' => '3.20',
            'weight_unit' => 'kg',
            'parcel_length' => '40.00',
            'parcel_width' => '28.00',
            'parcel_height' => '18.00',
            'parcel_dimension_unit' => 'cm',
            'shipped_at' => null,
            'delivered_at' => null,
        ];
    }
}
