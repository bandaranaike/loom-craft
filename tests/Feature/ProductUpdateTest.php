<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductMedia;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('allows approved vendors to view their product edit page', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create([
        'name' => 'Editable Loom Piece',
    ]);

    $this->actingAs($vendorUser)
        ->get(route('vendor.products.edit', $product))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/products/edit')
            ->where('product.id', $product->id)
            ->where('product.name', 'Editable Loom Piece')
        );
});

it('updates vendor owned products and recalculates selling price', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create([
        'name' => 'Original Name',
        'vendor_price' => '100.00',
        'selling_price' => '107.00',
        'status' => 'active',
    ]);
    $initialCategory = ProductCategory::factory()->create();
    $product->categories()->sync([$initialCategory->id]);
    $updatedCategories = ProductCategory::factory()->count(2)->create();

    $response = $this->actingAs($vendorUser)->patch(
        route('vendor.products.update', $product),
        [
            'name' => 'Updated Name',
            'description' => 'Updated description.',
            'vendor_price' => '200.00',
            'materials' => 'Cotton',
            'pieces_count' => 3,
            'production_time_days' => 14,
            'dimension_length' => 120,
            'dimension_width' => 40,
            'dimension_height' => 2,
            'dimension_unit' => 'cm',
            'category_ids' => $updatedCategories->pluck('id')->all(),
        ]
    );

    $response->assertRedirect(route('vendor.products.index'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'vendor_price' => '200.00',
        'commission_rate' => '7.00',
        'selling_price' => '214.00',
        'status' => 'active',
    ]);
    expect($product->fresh()->categories()->pluck('product_categories.id')->all())
        ->toEqualCanonicalizing($updatedCategories->pluck('id')->all());
});

it('prevents vendors from editing products they do not own', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $otherVendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $otherVendor = Vendor::factory()->for($otherVendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($otherVendor)->create();

    $this->actingAs($vendorUser)
        ->get(route('vendor.products.edit', $product))
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->patch(route('vendor.products.update', $product), [
            'name' => 'Not Allowed',
            'description' => 'Blocked.',
            'vendor_price' => '99.00',
            'category_ids' => ProductCategory::factory()->count(1)->create()->pluck('id')->all(),
        ])
        ->assertForbidden();
});

it('allows vendors to upload additional product images', function () {
    Storage::fake('public');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create();

    $response = $this->actingAs($vendorUser)->post(route('vendor.products.images.store', $product), [
        'images' => [
            UploadedFile::fake()->image('fresh-1.jpg'),
            UploadedFile::fake()->image('fresh-2.jpg'),
        ],
    ]);

    $response->assertRedirect(route('vendor.products.edit', $product));

    $this->assertSame(2, $product->media()->where('type', 'image')->count());

    $paths = $product->media()->where('type', 'image')->pluck('path')->all();
    foreach ($paths as $path) {
        Storage::disk('public')->assertExists($path);
    }
});

it('allows vendors to delete their product image', function () {
    Storage::fake('public');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create();
    $path = Storage::disk('public')->putFile(
        'products/images',
        UploadedFile::fake()->image('existing.jpg')
    );

    $image = ProductMedia::query()->create([
        'product_id' => $product->id,
        'type' => 'image',
        'path' => $path,
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($vendorUser)->delete(
        route('vendor.products.images.destroy', ['product' => $product->id, 'image' => $image->id])
    );

    $response->assertRedirect(route('vendor.products.edit', $product));

    $this->assertDatabaseMissing('product_media', [
        'id' => $image->id,
    ]);
    Storage::disk('public')->assertMissing($path);
});

it('prevents vendors from managing images on products they do not own', function () {
    Storage::fake('public');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $otherVendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $otherVendor = Vendor::factory()->for($otherVendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($otherVendor)->create();
    $path = Storage::disk('public')->putFile(
        'products/images',
        UploadedFile::fake()->image('foreign.jpg')
    );

    $image = ProductMedia::query()->create([
        'product_id' => $product->id,
        'type' => 'image',
        'path' => $path,
        'sort_order' => 0,
    ]);

    $this->actingAs($vendorUser)
        ->post(route('vendor.products.images.store', $product), [
            'images' => [UploadedFile::fake()->image('blocked.jpg')],
        ])
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->delete(route('vendor.products.images.destroy', ['product' => $product->id, 'image' => $image->id]))
        ->assertForbidden();
});
