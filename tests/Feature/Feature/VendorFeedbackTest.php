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
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vendor.feedback.create'));

    $response->assertForbidden();
});

test('approved vendors can submit feedback', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($user)->create([
        'status' => 'approved',
    ]);

    $response = $this->actingAs($user)->post(route('vendor.feedback.store'), [
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

    $response = $this->actingAs($user)->post(route('vendor.feedback.store'), [
        'title' => 'Blocked',
        'details' => 'This should not be accepted.',
    ]);

    $response->assertForbidden();
});
