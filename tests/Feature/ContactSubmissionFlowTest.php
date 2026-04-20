<?php

use App\Enums\ContactSubmissionStatus;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('contact us page is displayed with guest defaults', function () {
    $response = $this->get(route('contact.show'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('contact-us')
            ->where('formDefaults.name', '')
            ->where('formDefaults.email', '')
            ->where('formDefaults.phone', '')
        );
});

test('logged in users see contact form defaults from their profile', function () {
    $user = User::factory()->create([
        'name' => 'Jane Weaver',
        'email' => 'jane@example.com',
        'phone' => '+94 77 555 0101',
    ]);

    $response = $this->actingAs($user)->get(route('contact.show'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('formDefaults.name', 'Jane Weaver')
            ->where('formDefaults.email', 'jane@example.com')
            ->where('formDefaults.phone', '+94 77 555 0101')
        );
});

test('guests can submit contact messages', function () {
    $response = $this->from(route('contact.show'))
        ->post(route('contact.store'), [
            'name' => 'Alex Buyer',
            'email' => 'alex@example.com',
            'phone' => '',
            'message' => 'I need help with an order update.',
        ]);

    $response->assertRedirect(route('contact.show'));

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'Alex Buyer',
        'email' => 'alex@example.com',
        'phone' => null,
        'status' => ContactSubmissionStatus::New->value,
    ]);
});

test('logged in users can submit contact messages with only a phone number', function () {
    $user = User::factory()->create([
        'phone' => '+94 71 222 3333',
    ]);

    $response = $this->actingAs($user)
        ->from(route('contact.show'))
        ->post(route('contact.store'), [
            'name' => $user->name,
            'email' => '',
            'phone' => '+94 71 222 3333',
            'message' => 'Please call me back about shipping options.',
        ]);

    $response->assertRedirect(route('contact.show'));

    $this->assertDatabaseHas('contact_submissions', [
        'user_id' => $user->id,
        'phone' => '+94 71 222 3333',
        'email' => null,
    ]);
});

test('contact message validation requires name message and at least one contact method', function () {
    $response = $this->from(route('contact.show'))
        ->post(route('contact.store'), [
            'name' => '',
            'email' => '',
            'phone' => '',
            'message' => '',
        ]);

    $response
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrors(['name', 'email', 'phone', 'message']);
});
