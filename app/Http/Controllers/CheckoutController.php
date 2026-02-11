<?php

namespace App\Http\Controllers;

use App\Actions\Order\PlaceOrder;
use App\Actions\Order\ShowCheckout;
use App\DTOs\Cart\CartSessionData;
use App\DTOs\Order\CheckoutStoreData;
use App\Http\Requests\Order\StoreCheckoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class CheckoutController extends Controller
{
    public function show(Request $request, ShowCheckout $action): Response|RedirectResponse
    {
        $result = $action->handle(CartSessionData::fromRequest($request));

        if ($result->cart->itemCount === 0) {
            return redirect()
                ->route('cart.show')
                ->with('status', 'Your cart is empty.');
        }

        $response = Inertia::render('checkout', [
            ...$result->toArray(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);

        if ($request->user() === null && $result->guestToken !== null) {
            $response->withCookie(cookie('loomcraft_guest_token', $result->guestToken, 60 * 24 * 30));
        }

        return $response;
    }

    public function store(
        StoreCheckoutRequest $request,
        PlaceOrder $action,
    ): RedirectResponse {
        $result = $action->handle(CheckoutStoreData::fromRequest($request));

        if ($request->user() === null) {
            $request->session()->put('guest_order_id', $result->orderId);
        }

        $response = redirect()->route('orders.confirmation', ['order' => $result->orderId]);

        if ($request->user() === null && $result->guestToken !== null) {
            $response->withCookie(cookie('loomcraft_guest_token', $result->guestToken, 60 * 24 * 30));
        }

        return $response;
    }
}
