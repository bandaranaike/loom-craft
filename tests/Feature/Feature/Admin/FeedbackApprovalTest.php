<?php

use App\Models\Suggestion;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('guests are redirected from feedback approvals', function () {
    get(route('admin.feedback.pending'))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot view feedback approvals', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.feedback.pending'));

    $response->assertForbidden();
});

test('admins can view pending vendor feedback', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'display_name' => 'Kandyan Loom House',
    ]);
    $suggestion = Suggestion::factory()->for($vendorUser)->create([
        'status' => 'pending',
        'title' => 'Reliable international demand',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.feedback.pending'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/feedback/pending')
            ->has('feedback', 1)
            ->where('feedback.0.id', $suggestion->id)
            ->where('feedback.0.title', $suggestion->title)
        );
});

test('admins can approve vendor feedback', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);
    $suggestion = Suggestion::factory()->for($vendorUser)->create([
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.feedback.approve', $suggestion));

    $response->assertRedirect(route('admin.feedback.pending'));

    $this->assertDatabaseHas('suggestions', [
        'id' => $suggestion->id,
        'status' => 'approved',
        'handled_by' => $admin->id,
    ]);
});

test('approved feedback appears on the home page', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'display_name' => 'Woven Heritage Guild',
    ]);
    $suggestion = Suggestion::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'title' => 'Global orders improved',
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('vendor_feedback', 1)
            ->where('vendor_feedback.0.id', $suggestion->id)
            ->where('vendor_feedback.0.author_name', $vendor->display_name)
        );
});

test('admins can view pending buyer feedback', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'customer', 'name' => 'Buyer One']);

    $suggestion = Suggestion::factory()->for($buyer)->create([
        'status' => 'pending',
        'title' => 'Need saved filters',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.feedback.pending'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/feedback/pending')
            ->has('feedback', 1)
            ->where('feedback.0.id', $suggestion->id)
            ->where('feedback.0.author_name', 'Buyer One')
            ->where('feedback.0.author_role', 'customer')
        );
});
