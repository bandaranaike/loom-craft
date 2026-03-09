<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorContactSubmission;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can submit customer inquiries to approved vendors', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'slug' => 'craft-house',
    ]);

    $response = $this->from(route('vendors.show', ['vendor' => $vendor->slug]))
        ->post(route('vendors.inquiries.store', ['vendor' => $vendor->slug]), [
            'name' => 'Alex Buyer',
            'email' => 'alex@example.com',
            'phone' => '+1 555 123 1234',
            'subject' => 'Bulk custom order',
            'message' => 'I would like to discuss a recurring bulk order for handcrafted textiles.',
        ]);

    $response->assertRedirect(route('vendors.show', ['vendor' => $vendor->slug]));

    $this->assertDatabaseHas('vendor_contact_submissions', [
        'vendor_id' => $vendor->id,
        'name' => 'Alex Buyer',
        'email' => 'alex@example.com',
        'subject' => 'Bulk custom order',
        'status' => 'pending',
    ]);
});

test('inquiry submission redirects back to the product page when sent from there', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'slug' => 'craft-house',
    ]);
    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
    ]);

    $response = $this->from(route('products.show', $product))
        ->post(route('vendors.inquiries.store', ['vendor' => $vendor->slug]), [
            'name' => 'Alex Buyer',
            'email' => 'alex@example.com',
            'phone' => '+1 555 123 1234',
            'subject' => 'Product detail request',
            'message' => 'I would like more detail about materials, lead time, and shipping for this piece.',
        ]);

    $response->assertRedirect(route('products.show', $product));
});

test('inquiry submission validates required fields', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
        'slug' => 'loom-vendor',
    ]);

    $response = $this->from(route('vendors.show', ['vendor' => $vendor->slug]))
        ->post(route('vendors.inquiries.store', ['vendor' => $vendor->slug]), [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => 'Too short',
        ]);

    $response
        ->assertRedirect(route('vendors.show', ['vendor' => $vendor->slug]))
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});

test('approved vendors can view inquiries in dashboard', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);
    VendorContactSubmission::factory()->for($vendor)->create([
        'subject' => 'Hello vendor',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($vendorUser)->get(route('vendor.inquiries.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/inquiries/index')
            ->has('inquiries', 1)
            ->where('inquiries.0.subject', 'Hello vendor')
        );
});

test('admins can moderate pending inquiries', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = Vendor::factory()->create(['status' => 'approved']);
    $submission = VendorContactSubmission::factory()->for($vendor)->create([
        'status' => 'pending',
    ]);

    $listResponse = $this->actingAs($admin)->get(route('admin.vendor-inquiries.pending'));

    $listResponse
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/vendor-inquiries/pending')
            ->has('inquiries', 1)
        );

    $approveResponse = $this->actingAs($admin)->post(route('admin.vendor-inquiries.approve', $submission));

    $approveResponse->assertRedirect(route('admin.vendor-inquiries.pending'));

    $this->assertDatabaseHas('vendor_contact_submissions', [
        'id' => $submission->id,
        'status' => 'approved',
        'handled_by' => $admin->id,
    ]);
});

test('non-admin users cannot moderate inquiries', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $submission = VendorContactSubmission::factory()->create([
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->post(route('admin.vendor-inquiries.approve', $submission))
        ->assertForbidden();
});
