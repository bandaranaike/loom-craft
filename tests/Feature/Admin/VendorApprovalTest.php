<?php

use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('guests are redirected from pending vendor approvals', function () {
    get(route('admin.vendors.pending'))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot view pending vendor approvals', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.vendors.pending'));

    $response->assertForbidden();
});

test('admins can view pending vendor approvals', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = Vendor::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.vendors.pending'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/vendors/pending')
            ->has('vendors', 1)
            ->where('vendors.0.display_name', $vendor->display_name)
        );
});

test('admins can approve vendors', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = Vendor::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.vendors.approve', $vendor));

    $response->assertRedirect(route('admin.vendors.pending'));

    $this->assertDatabaseHas('vendors', [
        'id' => $vendor->id,
        'status' => 'approved',
        'approved_by' => $admin->id,
    ]);
});

test('admins can reject vendors with a reason', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = Vendor::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.vendors.reject', $vendor), [
        'reason' => 'Missing provenance documentation.',
    ]);

    $response->assertRedirect(route('admin.vendors.pending'));

    $this->assertDatabaseHas('vendors', [
        'id' => $vendor->id,
        'status' => 'rejected',
        'approved_by' => $admin->id,
    ]);
});

test('admins must provide a rejection reason', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = Vendor::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.vendors.reject', $vendor), [
        'reason' => '',
    ]);

    $response->assertSessionHasErrors(['reason']);
});

test('non-admin users cannot reject vendors', function () {
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.vendors.reject', $vendor), [
        'reason' => 'Not allowed',
    ]);

    $response->assertForbidden();
});
