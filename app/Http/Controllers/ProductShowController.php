<?php

namespace App\Http\Controllers;

use App\Actions\Product\ShowPublicProduct;
use App\DTOs\Product\ProductShowData;
use App\Http\Requests\Product\ShowProductRequest;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ProductShowController extends Controller
{
    public function show(
        ShowProductRequest $request,
        ShowPublicProduct $action,
    ): Response {
        $result = $action->handle(ProductShowData::fromRequest($request));

        return Inertia::render('products/show', [
            ...$result->toArray(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
