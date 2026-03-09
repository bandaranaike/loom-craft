<?php

use App\Models\Product;
use App\Models\ProductCategory;
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
    $category = ProductCategory::factory()->create([
        'name' => 'Wall Hangers',
        'slug' => 'wall-hangers',
    ]);
    $activeProduct->categories()->sync([$category->id]);
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
            ->where('products.0.categories.0.slug', 'wall-hangers')
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

test('users can filter public products by category slug', function () {
    $approvedVendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $matchingCategory = ProductCategory::factory()->create([
        'name' => 'Pillow Covers',
        'slug' => 'pillow-covers',
    ]);
    $otherCategory = ProductCategory::factory()->create([
        'name' => 'Wall Hangers',
        'slug' => 'wall-hangers',
    ]);

    $matchingProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Forest Pillow Cover',
    ]);
    $matchingProduct->categories()->sync([$matchingCategory->id]);

    $otherProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Textile Wall Hanging',
    ]);
    $otherProduct->categories()->sync([$otherCategory->id]);

    $response = $this->get(route('products.index', [
        'category' => 'pillow-covers',
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->has('products', 1)
            ->where('products.0.id', $matchingProduct->id)
            ->where('selected_category', 'pillow-covers')
        );
});
