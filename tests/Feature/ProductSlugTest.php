<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;

test('products generate unique slugs from their names', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $firstProduct = Product::factory()->for($vendor)->create([
        'name' => 'Heritage Runner',
    ]);
    $secondProduct = Product::factory()->for($vendor)->create([
        'name' => 'Heritage Runner',
    ]);

    expect($firstProduct->slug)->toBe('heritage-runner');
    expect($secondProduct->slug)->toBe('heritage-runner-2');
});

test('products refresh their slug when the name changes', function () {
    $product = Product::factory()->create([
        'name' => 'Original Heritage Runner',
    ]);

    $product->update([
        'name' => 'Renamed Heritage Runner',
    ]);

    expect($product->fresh()->slug)->toBe('renamed-heritage-runner');
});

test('public product route uses the slug path', function () {
    $product = Product::factory()->create([
        'name' => 'Gallery Runner',
    ]);

    expect(route('products.show', ['product' => $product->slug]))
        ->toEndWith('/product/gallery-runner');
});
