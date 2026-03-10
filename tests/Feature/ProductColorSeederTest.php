<?php

use App\Models\ProductColor;
use Database\Seeders\ProductColorSeeder;
use Illuminate\Support\Facades\File;

test('product color seeder uses the shared product color registry', function () {
    $definitions = json_decode(
        File::get(resource_path('data/product-colors.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($definitions)->toBeArray()->not->toBeEmpty();

    $this->seed(ProductColorSeeder::class);

    expect(ProductColor::query()->count())->toBe(count($definitions));

    foreach ($definitions as $sortOrder => $definition) {
        $this->assertDatabaseHas('product_colors', [
            'name' => $definition['name'],
            'slug' => $definition['slug'],
            'is_active' => true,
            'sort_order' => $sortOrder,
        ]);
    }
});
