<?php

namespace App\Http\Controllers;

use App\Actions\Cart\AddCartItem;
use App\Actions\Cart\RemoveCartItem;
use App\Actions\Cart\UpdateCartItem;
use App\DTOs\Cart\CartItemRemoveData;
use App\DTOs\Cart\CartItemStoreData;
use App\DTOs\Cart\CartItemUpdateData;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function store(
        StoreCartItemRequest $request,
        AddCartItem $action,
    ): RedirectResponse {
        $result = $action->handle(CartItemStoreData::fromRequest($request));

        $response = redirect()->route('cart.show');

        if ($request->user() === null && $result->guestToken !== null) {
            $response->withCookie(cookie('loomcraft_guest_token', $result->guestToken, 60 * 24 * 30));
        }

        return $response;
    }

    public function update(
        UpdateCartItemRequest $request,
        int $cartItem,
        UpdateCartItem $action,
    ): RedirectResponse {
        $action->handle(CartItemUpdateData::fromRequest($request, $cartItem));

        return redirect()->route('cart.show');
    }

    public function destroy(
        Request $request,
        int $cartItem,
        RemoveCartItem $action,
    ): RedirectResponse {
        $action->handle(CartItemRemoveData::fromRequest($request, $cartItem));

        return redirect()->route('cart.show');
    }
}
