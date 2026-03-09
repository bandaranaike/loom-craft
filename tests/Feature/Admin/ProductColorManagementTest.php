<?php

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('admins can view product color management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ProductColor::factory()->create([
        'name' => 'Beige',
        'slug' => 'beige',
        'sort_order' => 2,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.product-colors.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/product-colors/index')
            ->where('colors.0.slug', 'beige')
        );
});

test('admins can create product colors', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->post(route('admin.product-colors.store'), [
            'name' => 'Indigo',
            'is_active' => true,
            'sort_order' => 3,
        ]);

    $response->assertRedirect(route('admin.product-colors.index'));

    $this->assertDatabaseHas('product_colors', [
        'name' => 'Indigo',
        'slug' => 'indigo',
        'is_active' => true,
        'sort_order' => 3,
    ]);
});

test('admins can update and archive colors', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $color = ProductColor::factory()->create([
        'name' => 'Brown',
        'slug' => 'brown',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->patch(route('admin.product-colors.update', $color), [
            'name' => 'Deep Brown',
            'slug' => 'deep-brown',
            'is_active' => false,
            'sort_order' => 8,
        ]);

    $response->assertRedirect(route('admin.product-colors.index'));

    $this->assertDatabaseHas('product_colors', [
        'id' => $color->id,
        'name' => 'Deep Brown',
        'slug' => 'deep-brown',
        'is_active' => false,
        'sort_order' => 8,
    ]);
});

test('admins can delete product colors when not in use', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $color = ProductColor::factory()->create([
        'name' => 'Delete Me',
        'slug' => 'delete-me',
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('admin.product-colors.destroy', $color));

    $response->assertRedirect(route('admin.product-colors.index'));

    $this->assertDatabaseMissing('product_colors', [
        'id' => $color->id,
    ]);
});

test('in use product colors are not deleted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendorUser = User::factory()->create(['role' => 'vendor']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $color = ProductColor::factory()->create([
        'name' => 'Protected',
        'slug' => 'protected',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
    ]);
    $product->colors()->sync([$color->id]);

    $response = $this->actingAs($admin)
        ->delete(route('admin.product-colors.destroy', $color));

    $response->assertRedirect(route('admin.product-colors.index'));

    $this->assertDatabaseHas('product_colors', [
        'id' => $color->id,
    ]);
});

test('non-admin users cannot manage product colors', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $color = ProductColor::factory()->create();

    $this->actingAs($vendorUser)
        ->get(route('admin.product-colors.index'))
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->post(route('admin.product-colors.store'), [
            'name' => 'Blocked',
        ])
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->patch(route('admin.product-colors.update', $color), [
            'name' => 'Blocked Update',
            'is_active' => true,
        ])
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->delete(route('admin.product-colors.destroy', $color))
        ->assertForbidden();
});
