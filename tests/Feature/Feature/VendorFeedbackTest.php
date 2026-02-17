<?php

use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from vendor feedback page', function () {
    $this->get(route('vendor.feedback.create'))
        ->assertRedirect(route('login'));
});

test('approved vendors can view feedback page', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($user)->create([
        'status' => 'approved',
    ]);

    $response = $this->actingAs($user)->get(route('vendor.feedback.create'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/feedback/create')
        );
});

test('non-vendor users cannot view feedback page', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($user)->get(route('vendor.feedback.create'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/feedback/create')
        );
});

test('approved vendors can submit feedback', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($user)->create([
        'status' => 'approved',
    ]);

    $response = $this->actingAs($user)
        ->from(route('vendor.feedback.create'))
        ->post(route('vendor.feedback.store'), [
            'title' => 'Consistent buyer demand',
            'details' => 'Our studio has received steady weekly orders.',
        ]);

    $response->assertRedirect(route('vendor.feedback.create'));

    $this->assertDatabaseHas('suggestions', [
        'user_id' => $user->id,
        'title' => 'Consistent buyer demand',
        'status' => 'pending',
        'handled_by' => null,
    ]);
});

test('unapproved vendors cannot submit feedback', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($user)->create([
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->from(route('vendor.feedback.create'))
        ->post(route('vendor.feedback.store'), [
            'title' => 'Blocked',
            'details' => 'This should not be accepted.',
        ]);

    $response->assertForbidden();
});

test('customers can submit feedback', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)
        ->from(route('vendor.feedback.create'))
        ->post(route('vendor.feedback.store'), [
            'title' => 'Smooth checkout experience',
            'details' => 'The product details and checkout steps were clear.',
        ]);

    $response->assertRedirect(route('vendor.feedback.create'));

    $this->assertDatabaseHas('suggestions', [
        'user_id' => $customer->id,
        'title' => 'Smooth checkout experience',
        'status' => 'pending',
    ]);
});

test('submitting feedback again updates existing feedback instead of creating a second record', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->from(route('vendor.feedback.create'))
        ->post(route('vendor.feedback.store'), [
            'title' => 'First note',
            'details' => 'First details',
        ])->assertRedirect(route('vendor.feedback.create'));

    $this->actingAs($customer)
        ->from(route('vendor.feedback.create'))
        ->post(route('vendor.feedback.store'), [
            'title' => 'Updated note',
            'details' => 'Updated details',
        ])->assertRedirect(route('vendor.feedback.create'));

    $this->assertDatabaseCount('suggestions', 1);
    $this->assertDatabaseHas('suggestions', [
        'user_id' => $customer->id,
        'title' => 'Updated note',
        'details' => 'Updated details',
        'status' => 'pending',
    ]);
});
