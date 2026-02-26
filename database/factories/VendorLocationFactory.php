<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VendorLocation>
 */
class VendorLocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\VendorLocation>
     */
    protected $model = VendorLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'location_name' => fake()->company().' Atelier',
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'region' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->country(),
            'phone' => fake()->optional()->phoneNumber(),
            'hours' => fake()->optional()->randomElement(['Mon-Fri 9AM-5PM', 'Daily 10AM-6PM']),
            'map_url' => fake()->optional()->url(),
            'is_primary' => false,
        ];
    }
}
