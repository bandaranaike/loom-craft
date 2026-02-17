<?php

use App\Models\Suggestion;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated users receive their existing feedback on the home page', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $suggestion = Suggestion::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'title' => 'Original title',
        'details' => 'Original details',
    ]);

    $this->actingAs($vendorUser)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('my_feedback.id', $suggestion->id)
            ->where('my_feedback.title', 'Original title')
            ->where('my_feedback.details', 'Original details')
            ->where('my_feedback.status', 'approved')
        );
});

test('editing feedback updates the existing row and sends it back to pending review', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    Suggestion::factory()->for($customer)->create([
        'status' => 'approved',
        'title' => 'Before edit',
        'details' => 'Before details',
    ]);

    $this->actingAs($customer)
        ->from(route('home'))
        ->post(route('vendor.feedback.store'), [
            'title' => 'After edit',
            'details' => 'After details',
        ])
        ->assertRedirect(route('home'));

    $this->assertDatabaseCount('suggestions', 1);
    $this->assertDatabaseHas('suggestions', [
        'user_id' => $customer->id,
        'title' => 'After edit',
        'details' => 'After details',
        'status' => 'pending',
        'handled_by' => null,
    ]);
});
