<?php

namespace App\Http\Controllers;

use App\Actions\Product\ShowPublicProduct;
use App\Models\Cart;
use App\DTOs\Product\ProductShowData;
use App\Models\Product;
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
        $cartCurrency = Cart::query()
            ->when(
                $request->user() !== null,
                fn ($query) => $query->where('user_id', $request->user()->id),
                fn ($query) => $query->where('guest_token', $request->cookie('loomcraft_guest_token')),
            )
            ->value('currency');

        return Inertia::render('products/show', [
            ...$result->toArray(),
            'cartCurrency' => $cartCurrency ?? 'LKR',
            'canRegister' => Features::enabled(Features::registration()),
            'status' => session('status'),
        ]);
    }
}
