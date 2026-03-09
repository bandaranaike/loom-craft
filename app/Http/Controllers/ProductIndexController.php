<?php

namespace App\Http\Controllers;

use App\Actions\Product\ListPublicProducts;
use App\DTOs\Product\ProductPublicIndexData;
use App\Http\Requests\Product\IndexPublicProductsRequest;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ProductIndexController extends Controller
{
    public function index(
        IndexPublicProductsRequest $request,
        ListPublicProducts $action,
    ): Response {
        $result = $action->handle(ProductPublicIndexData::fromRequest($request));

        return Inertia::render('products/index', [
            ...$result->toArray(),
            'canRegister' => Features::enabled(Features::registration()),
            'search' => $request->string('search')->toString() ?: null,
            'selected_category' => $request->string('category')->toString() ?: null,
            'selected_vendor' => $request->string('vendor')->toString() ?: null,
            'selected_colors' => array_values(array_filter(
                $request->array('colors'),
                static fn (mixed $color): bool => is_string($color) && $color !== ''
            )),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'per_page' => $request->integer('per_page') ?: 9,
        ]);
    }
}
