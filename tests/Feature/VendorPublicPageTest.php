<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorLocation;
use Inertia\Testing\AssertableInertia as Assert;

test('public users can view an approved vendor storefront', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'slug' => 'woven-atelier',
        'display_name' => 'Woven Atelier',
    ]);

    VendorLocation::factory()->for($vendor)->create([
        'location_name' => 'Main Studio',
    ]);

    $category = ProductCategory::factory()->create([
        'name' => 'Sarongs',
        'slug' => 'sarongs',
    ]);

    $activeProduct = Product::factory()->for($vendor)->create([
        'name' => 'Sunset Loom Wrap',
        'status' => 'active',
    ]);
    $activeProduct->categories()->attach($category->id);

    Product::factory()->for($vendor)->create([
        'name' => 'Draft Pattern',
        'status' => 'pending_review',
    ]);

    $response = $this->get(route('vendors.show', ['vendor' => $vendor->slug]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendors/show')
            ->where('vendor.display_name', 'Woven Atelier')
            ->has('products', 1)
            ->where('products.0.name', 'Sunset Loom Wrap')
            ->has('categories', 1)
            ->where('categories.0.slug', 'sarongs')
            ->has('vendor.locations', 1)
        );
});

test('public users cannot view unapproved vendor storefronts', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'pending',
        'slug' => 'pending-vendor',
    ]);

    $this->get(route('vendors.show', ['vendor' => $vendor->slug]))
        ->assertNotFound();
});
