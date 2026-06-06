<?php

use App\Models\Product;
use App\Services\ProductPreparationEstimator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns no preparation time when requested quantity is fully in stock', function () {
    config()->set('commerce.production_time_buffer_rate', 0.10);

    $product = Product::factory()->create([
        'pieces_count' => 5,
        'production_time_days' => 3,
    ]);

    $estimate = app(ProductPreparationEstimator::class)->forProduct($product, 4);

    expect($estimate->shortageQuantity)->toBe(0)
        ->and($estimate->setupDays)->toBe(0)
        ->and($estimate->weavingDays)->toBe(0.0)
        ->and($estimate->totalDays)->toBe(0)
        ->and($estimate->message)->toBeNull();
});

it('calculates preparation time from shortage quantity only', function () {
    config()->set('commerce.production_time_setup_days', 2);
    config()->set('commerce.production_time_buffer_rate', 0.10);

    $product = Product::factory()->create([
        'pieces_count' => 2,
        'production_time_days' => 3,
    ]);

    $estimate = app(ProductPreparationEstimator::class)->forProduct($product, 5);

    expect($estimate->shortageQuantity)->toBe(3)
        ->and($estimate->setupDays)->toBe(2)
        ->and($estimate->weavingDays)->toBe(9.0)
        ->and($estimate->bufferDays)->toBe(2)
        ->and($estimate->totalDays)->toBe(13)
        ->and($estimate->message)->toContain('3 pieces')
        ->and($estimate->message)->toContain('13 days');
});

it('uses the most time-consuming product for cart preparation time', function () {
    $estimator = app(ProductPreparationEstimator::class);

    $firstProduct = Product::factory()->create([
        'pieces_count' => 0,
        'production_time_days' => 2,
    ]);
    $secondProduct = Product::factory()->create([
        'pieces_count' => 0,
        'production_time_days' => 5,
    ]);

    $cartEstimate = $estimator->forCart(collect([
        $estimator->forProduct($firstProduct, 2),
        $estimator->forProduct($secondProduct, 2),
    ]));

    expect($cartEstimate->totalDays)->toBe(
        max(
            $estimator->forProduct($firstProduct, 2)->totalDays,
            $estimator->forProduct($secondProduct, 2)->totalDays,
        )
    )
        ->and($cartEstimate->message)->toContain('all product preparation runs in parallel');
});

it('appends a large cart workload warning when the threshold is exceeded', function () {
    config()->set('commerce.production_time_large_cart_threshold', 1);

    $estimator = app(ProductPreparationEstimator::class);
    $products = Product::factory()
        ->count(2)
        ->create([
            'pieces_count' => 3,
            'production_time_days' => 2,
        ]);

    $cartEstimate = $estimator->forCart($products->map(
        fn (Product $product) => $estimator->forProduct($product, 1),
    ));

    expect($cartEstimate->exceedsLargeCartThreshold)->toBeTrue()
        ->and($cartEstimate->workloadWarningMessage)->toContain('may take longer than expected')
        ->and($cartEstimate->message)->toContain('may take longer than expected');
});
