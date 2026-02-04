<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard surfaces flash status messages', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->withSession([
        'status' => 'Your vendor application has been submitted for review.',
    ])->get(route('dashboard'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('status', 'Your vendor application has been submitted for review.')
        );
});
