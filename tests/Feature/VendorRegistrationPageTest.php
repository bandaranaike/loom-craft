<?php

use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('vendor register page requires authentication', function () {
    get(route('vendor.register'))
        ->assertRedirect(route('login'));
});

test('vendor register page renders for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vendor.register'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/register')
            ->has('status')
        );
});

test('authenticated users can submit vendor applications', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('vendor.register.store'), [
        'display_name' => 'Loom Atelier',
        'bio' => 'Generational Dumbara Rataa artisanship.',
        'location' => 'Kandy, Sri Lanka',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('vendors', [
        'user_id' => $user->id,
        'display_name' => 'Loom Atelier',
        'bio' => 'Generational Dumbara Rataa artisanship.',
        'location' => 'Kandy, Sri Lanka',
        'status' => 'pending',
    ]);
});

test('users cannot submit a second vendor application', function () {
    $vendor = Vendor::factory()->create();

    $response = $this->actingAs($vendor->user)->post(route('vendor.register.store'), [
        'display_name' => 'Second Studio',
        'bio' => 'Another attempt.',
        'location' => 'Matale, Sri Lanka',
    ]);

    $response->assertForbidden();
});
