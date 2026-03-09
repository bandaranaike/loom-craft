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

test('public vendor storefront hides website and contact details when visibility is disabled', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
        'slug' => 'private-loom',
        'website_url' => 'https://example.com',
        'contact_email' => 'vendor@example.com',
        'contact_phone' => '+94 77 123 4567',
        'whatsapp_number' => '+94 77 123 4567',
        'is_contact_public' => false,
        'is_website_public' => false,
    ]);

    $this->get(route('vendors.show', ['vendor' => $vendor->slug]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendors/show')
            ->where('vendor.website_url', null)
            ->where('vendor.contact_email', null)
            ->where('vendor.contact_phone', null)
            ->where('vendor.whatsapp_number', null)
        );
});
