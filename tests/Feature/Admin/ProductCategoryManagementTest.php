<?php

use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('admins can view product category management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ProductCategory::factory()->create([
        'name' => 'Wall Hangers',
        'slug' => 'wall-hangers',
        'sort_order' => 2,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.product-categories.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/product-categories/index')
            ->where('categories.0.slug', 'wall-hangers')
        );
});

test('admins can create product categories and receive unique slug', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ProductCategory::factory()->create([
        'name' => 'Pillow Covers',
        'slug' => 'pillow-covers',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.product-categories.store'), [
            'name' => 'Premium Pillow Covers',
            'slug' => 'pillow-covers',
            'description' => 'Seasonal pillow collection',
            'is_active' => true,
            'sort_order' => 5,
        ]);

    $response->assertRedirect(route('admin.product-categories.index'));

    $this->assertDatabaseHas('product_categories', [
        'name' => 'Premium Pillow Covers',
        'slug' => 'pillow-covers-2',
        'is_active' => true,
        'sort_order' => 5,
    ]);
});

test('admins can update and archive categories', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $category = ProductCategory::factory()->create([
        'name' => 'Wall Hangers',
        'slug' => 'wall-hangers',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->patch(route('admin.product-categories.update', $category), [
            'name' => 'Wall Art Hangings',
            'slug' => 'wall-art-hangings',
            'description' => 'Updated category description.',
            'is_active' => false,
            'sort_order' => 9,
        ]);

    $response->assertRedirect(route('admin.product-categories.index'));

    $this->assertDatabaseHas('product_categories', [
        'id' => $category->id,
        'name' => 'Wall Art Hangings',
        'slug' => 'wall-art-hangings',
        'is_active' => false,
        'sort_order' => 9,
    ]);
});

test('non-admin users cannot manage product categories', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $category = ProductCategory::factory()->create();

    $this->actingAs($vendorUser)
        ->get(route('admin.product-categories.index'))
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->post(route('admin.product-categories.store'), [
            'name' => 'Blocked',
        ])
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->patch(route('admin.product-categories.update', $category), [
            'name' => 'Blocked Update',
            'is_active' => true,
        ])
        ->assertForbidden();
});

test('category creation requires unique name', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ProductCategory::factory()->create([
        'name' => 'Cushion Covers',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.product-categories.index'))
        ->post(route('admin.product-categories.store'), [
            'name' => 'Cushion Covers',
            'is_active' => true,
        ]);

    $response
        ->assertRedirect(route('admin.product-categories.index'))
        ->assertSessionHasErrors(['name']);
});
