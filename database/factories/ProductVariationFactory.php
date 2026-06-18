<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariation>
 */
class ProductVariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'label' => fake()->randomElement(['16*16', '18*18', '20*20']),
            'vendor_price' => fake()->randomFloat(2, 1000, 5000),
            'selling_price' => fake()->randomFloat(2, 1200, 6000),
            'dimension_length' => fake()->randomFloat(2, 10, 200),
            'dimension_width' => fake()->randomFloat(2, 10, 200),
            'dimension_height' => fake()->randomFloat(2, 1, 50),
            'sort_order' => 0,
        ];
    }
}
