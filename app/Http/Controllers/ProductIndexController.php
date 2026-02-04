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
            'per_page' => $request->integer('per_page') ?: 9,
        ]);
    }
}
