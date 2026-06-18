<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            if ($product->variations()->exists()) {
                return;
            }

            $product->variations()->create([
                'label' => 'Standard',
                'vendor_price' => $product->vendor_price,
                'selling_price' => $product->selling_price,
                'dimension_length' => fake()->randomFloat(2, 10, 200),
                'dimension_width' => fake()->randomFloat(2, 10, 200),
                'dimension_height' => fake()->randomFloat(2, 1, 50),
                'sort_order' => 0,
            ]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'product_code' => strtoupper(fake()->unique()->bothify('LC-#####-???')),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'vendor_price' => fake()->randomFloat(2, 50, 5000),
            'commission_rate' => 7.00,
            'selling_price' => fake()->randomFloat(2, 60, 5500),
            'discount_percentage' => null,
            'materials' => fake()->words(4, true),
            'pieces_count' => fake()->numberBetween(1, 10),
            'production_time_days' => fake()->numberBetween(7, 60),
            'dimension_unit' => 'cm',
            'dead_weight' => fake()->randomFloat(2, 0.1, 10),
            'dead_weight_unit' => 'kg',
            'status' => 'pending_review',
        ];
    }
}
