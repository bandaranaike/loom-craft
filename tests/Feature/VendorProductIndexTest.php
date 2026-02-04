<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows approved vendors to view their products', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $ownProduct = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'name' => 'My Loom Set',
    ]);

    $otherVendor = Vendor::factory()->create();

    Product::factory()->create([
        'vendor_id' => $otherVendor->id,
        'name' => 'Other Loom Set',
    ]);

    $this->actingAs($vendorUser)
        ->get(route('vendor.products.index'))
        ->assertSuccessful()
        ->assertSee($ownProduct->name)
        ->assertDontSee('Other Loom Set');
});

it('blocks customers from the vendor product list', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('vendor.products.index'))
        ->assertForbidden();
});
