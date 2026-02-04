<?php

use App\Models\Product;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view approved active products', function () {
    $approvedVendor = Vendor::factory()->create([
        'status' => 'approved',
        'display_name' => 'Heritage Loom Atelier',
    ]);
    $activeProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Dumbara Signature Weave',
    ]);
    Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'pending_review',
    ]);

    $response = $this->get(route('products.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->where('products.0.id', $activeProduct->id)
            ->where('products.0.name', $activeProduct->name)
            ->where('products.0.vendor_name', $approvedVendor->display_name)
        );
});

test('products from unapproved vendors are not visible', function () {
    $pendingVendor = Vendor::factory()->create([
        'status' => 'pending',
    ]);
    $product = Product::factory()->create([
        'vendor_id' => $pendingVendor->id,
        'status' => 'active',
    ]);

    $response = $this->get(route('products.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->where('products', [])
        );

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'status' => 'active',
    ]);
});
