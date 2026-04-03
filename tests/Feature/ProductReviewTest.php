<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

it('shows published product reviews and allows delivered customers to post once', function () {
    $product = createReviewableProduct();

    $firstReviewer = User::factory()->create(['name' => 'Ayesha Fernando']);
    $secondReviewer = User::factory()->create(['name' => 'Nimal Perera']);
    $eligibleCustomer = User::factory()->create();

    createProductOrder($firstReviewer, $product);
    createProductOrder($secondReviewer, $product);
    createProductOrder($eligibleCustomer, $product);

    ProductReview::query()->create([
        'product_id' => $product->id,
        'user_id' => $firstReviewer->id,
        'rating' => 5,
        'review' => 'The weave is crisp, the finishing is precise, and it arrived exactly as presented.',
    ]);

    ProductReview::query()->create([
        'product_id' => $product->id,
        'user_id' => $secondReviewer->id,
        'rating' => 4,
        'review' => 'Beautiful texture and colour depth, with careful packing and a premium unboxing feel.',
    ]);

    $this->actingAs($eligibleCustomer)
        ->get(route('products.show', ['product' => $product->slug]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/show')
            ->where('review_summary.average_rating', '4.5')
            ->where('review_summary.total_reviews', 2)
            ->where('review_form.can_submit', true)
            ->where('review_form.has_delivered_purchase', true)
            ->where('review_form.has_reviewed', false)
            ->where('reviews', function (mixed $reviews): bool {
                $resolvedReviews = collect($reviews);

                return $resolvedReviews->count() === 2
                    && $resolvedReviews->contains(fn ($review): bool => $review['reviewer_name'] === 'Ayesha Fernando' && $review['rating'] === 5)
                    && $resolvedReviews->contains(fn ($review): bool => $review['reviewer_name'] === 'Nimal Perera' && $review['rating'] === 4);
            })
        );
});

it('allows a delivered customer to submit a single product review', function () {
    $customer = User::factory()->create();
    $product = createReviewableProduct();

    createProductOrder($customer, $product);

    $this->actingAs($customer)
        ->post(route('products.reviews.store', ['product' => $product->slug]), [
            'rating' => 5,
            'review' => 'Outstanding craftsmanship, rich colour, and a finish that feels worthy of a collector display.',
        ])
        ->assertRedirect(route('products.show', ['product' => $product->slug]))
        ->assertSessionHas('review_status');

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $product->id,
        'user_id' => $customer->id,
        'rating' => 5,
    ]);
});

it('forbids review submissions before delivery is completed', function () {
    $customer = User::factory()->create();
    $product = createReviewableProduct();

    createProductOrder($customer, $product, status: 'shipped');

    $this->actingAs($customer)
        ->post(route('products.reviews.store', ['product' => $product->slug]), [
            'rating' => 4,
            'review' => 'The piece looks promising, but I should not be able to review it before delivery.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('product_reviews', 0);
});

function createReviewableProduct(): Product
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    return Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);
}

function createProductOrder(User $customer, Product $product, string $status = 'delivered'): Order
{
    $order = Order::query()->create([
        'user_id' => $customer->id,
        'status' => $status,
        'currency' => 'LKR',
        'subtotal' => '180.00',
        'commission_total' => '180.00',
        'total' => '180.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'vendor_id' => $product->vendor_id,
        'quantity' => 1,
        'unit_price' => '180.00',
        'commission_rate' => '100.00',
        'commission_amount' => '180.00',
        'line_total' => '180.00',
    ]);

    return $order;
}
