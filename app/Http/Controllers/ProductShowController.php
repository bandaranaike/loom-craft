<?php

namespace App\Http\Controllers;

use App\Actions\Product\ShowPublicProduct;
use App\DTOs\Product\ProductShowData;
use App\Models\Product;
use App\ValueObjects\Currency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ProductShowController extends Controller
{
    public function show(
        Request $request,
        Product $product,
        ShowPublicProduct $action,
    ): Response {
        $result = $action->handle(ProductShowData::fromModel($request, $product));

        return Inertia::render('products/show', [
            ...$result->toArray(),
            'cartCurrency' => Currency::default()->code,
            'productionEstimateConfig' => [
                'setup_days' => (int) config('commerce.production_time_setup_days', 2),
                'buffer_rate' => (float) config('commerce.production_time_buffer_rate', 0.10),
                'default_weaving_days' => (float) config('commerce.production_time_default_weaving_days', 1),
                'max_display_days' => (int) config('commerce.production_time_max_display_days', 60),
            ],
            'canRegister' => Features::enabled(Features::registration()),
            'status' => session('status'),
            'reviewStatus' => session('review_status'),
        ]);
    }
}
