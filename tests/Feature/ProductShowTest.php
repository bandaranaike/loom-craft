<?php

use App\Models\Product;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view active products', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
        'display_name' => 'Heritage Loom Atelier',
    ]);
    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'active',
        'name' => 'Dumbara Signature Weave',
    ]);

    $response = $this->get(route('products.show', $product));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/show')
            ->where('product.id', $product->id)
            ->where('product.name', $product->name)
            ->where('product.vendor.display_name', $vendor->display_name)
        );
});

test('non-active products are not visible to guests', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'pending_review',
    ]);

    $response = $this->get(route('products.show', $product));

    $response->assertNotFound();
});
