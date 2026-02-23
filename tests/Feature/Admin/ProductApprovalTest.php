<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('guests are redirected from product approvals', function () {
    get(route('admin.products.pending'))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot view pending product approvals', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.products.pending'));

    $response->assertForbidden();
});

test('admins can view pending review products', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $pendingProduct = Product::factory()->create([
        'name' => 'Pending Runner',
        'status' => 'pending_review',
    ]);

    Product::factory()->create([
        'name' => 'Already Active Runner',
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.products.pending'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/products/pending')
            ->has('products', 1)
            ->where('products.0.id', $pendingProduct->id)
            ->where('products.0.name', $pendingProduct->name)
            ->where('products.0.status', 'pending_review')
        );
});

test('admins can approve pending review products', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $product = Product::factory()->create([
        'status' => 'pending_review',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.products.approve', $product));

    $response->assertRedirect(route('admin.products.pending'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'status' => 'active',
    ]);
});

test('non-admin users cannot approve products', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->create([
        'status' => 'pending_review',
    ]);

    $response = $this->actingAs($vendorUser)->post(route('admin.products.approve', $product));

    $response->assertForbidden();
});
