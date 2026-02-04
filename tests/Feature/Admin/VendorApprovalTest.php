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

test('admins can search pending vendors by display name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $matching = Vendor::factory()->create(['display_name' => 'Heritage Looms']);
    Vendor::factory()->create(['display_name' => 'Sunrise Weaves']);

    $response = $this->actingAs($admin)->get(route('admin.vendors.pending', [
        'search' => 'Heritage',
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/vendors/pending')
            ->has('vendors', 1)
            ->where('vendors.0.display_name', $matching->display_name)
            ->where('search', 'Heritage')
        );
});

test('pending vendors list is paginated', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Vendor::factory()->count(12)->create();

    $response = $this->actingAs($admin)->get(route('admin.vendors.pending'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/vendors/pending')
            ->has('vendors', 10)
            ->where('pagination.total', 12)
            ->where('pagination.per_page', 10)
            ->where('pagination.current_page', 1)
        );
});

test('pending vendors list can change per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Vendor::factory()->count(26)->create();

    $response = $this->actingAs($admin)->get(route('admin.vendors.pending', [
        'per_page' => 25,
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/vendors/pending')
            ->has('vendors', 25)
            ->where('pagination.total', 26)
            ->where('pagination.per_page', 25)
            ->where('per_page', 25)
        );
});

test('per page selection is persisted in a cookie', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Vendor::factory()->count(26)->create();

    $response = $this->actingAs($admin)->get(route('admin.vendors.pending', [
        'per_page' => 25,
    ]));

    $response->assertCookie('vendor_pending_per_page', '25');
});

test('search and per page persist across pagination links', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Vendor::factory()->count(26)->create(['display_name' => 'Heritage Loom']);

    $response = $this->actingAs($admin)->get(route('admin.vendors.pending', [
        'search' => 'Heritage',
        'per_page' => 10,
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/vendors/pending')
            ->where('search', 'Heritage')
            ->where('per_page', 10)
            ->where('pagination.links.1.url', fn (string $url) => str_contains($url, 'search=Heritage'))
            ->where('pagination.links.1.url', fn (string $url) => str_contains($url, 'per_page=10'))
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

    $this->assertDatabaseHas('users', [
        'id' => $vendor->user_id,
        'role' => 'vendor',
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
