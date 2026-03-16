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
            'canRegister' => Features::enabled(Features::registration()),
            'status' => session('status'),
        ]);
    }
}
