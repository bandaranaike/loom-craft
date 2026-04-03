<?php

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('home page renders', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'name' => 'Signed Heritage Textile',
    ]);
    $color = ProductColor::factory()->create([
        'name' => 'Beige',
        'slug' => 'beige',
    ]);
    $product->colors()->sync([$color->id]);

    get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('canRegister')
            ->has('latest_products', 1)
            ->where('latest_products.0.id', $product->id)
            ->where('latest_products.0.name', $product->name)
            ->where('latest_products.0.categories', [])
            ->where('latest_products.0.colors.0.slug', 'beige')
        );
});

test('home page shows up to six random active products from approved vendors', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $excludedVendorUser = User::factory()->create(['role' => 'vendor']);
    $excludedVendor = Vendor::factory()->for($excludedVendorUser)->create([
        'status' => 'pending',
    ]);

    $visibleProducts = [];

    foreach (range(1, 9) as $index) {
        $product = Product::factory()->for($vendor)->create([
            'status' => 'active',
            'name' => "Visible Product {$index}",
            'created_at' => now()->subMinutes(10 - $index),
            'updated_at' => now()->subMinutes(10 - $index),
        ]);

        $visibleProducts[] = $product;
    }

    Product::factory()->for($excludedVendor)->create([
        'status' => 'active',
        'name' => 'Excluded Vendor Product',
    ]);

    $response = get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('latest_products', 6)
        );

    $visibleProductIds = collect($visibleProducts)
        ->pluck('id')
        ->values()
        ->all();

    $homeProductIds = collect($response->viewData('page')['props']['latest_products'])
        ->pluck('id')
        ->values()
        ->all();

    expect($homeProductIds)
        ->toHaveCount(6);

    foreach ($homeProductIds as $productId) {
        expect($visibleProductIds)->toContain($productId);
    }
});
