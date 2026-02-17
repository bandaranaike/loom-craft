<?php

use App\Models\Product;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('home page renders', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'display_name' => 'Dumbara Atelier',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'name' => 'Signed Heritage Textile',
    ]);

    $suggestion = Suggestion::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'title' => 'Transparent curation support',
        'details' => 'Approval flow has helped our studio scale production.',
    ]);

    get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('canRegister')
            ->where('atelier_ledger.active_products', 1)
            ->where('atelier_ledger.approved_feedback', 1)
            ->has('vendor_feedback', 1)
            ->where('vendor_feedback.0.id', $suggestion->id)
            ->where('vendor_feedback.0.vendor_name', $vendor->display_name)
            ->has('latest_products', 1)
            ->where('latest_products.0.id', $product->id)
            ->where('latest_products.0.name', $product->name)
        );
});
