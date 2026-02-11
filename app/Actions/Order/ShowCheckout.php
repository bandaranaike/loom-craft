<?php

namespace App\Actions\Order;

use App\Actions\Cart\ShowCart;
use App\DTOs\Cart\CartSessionData;
use App\DTOs\Order\CheckoutViewResult;
use App\Models\Cart;
use Illuminate\Support\Facades\Gate;

class ShowCheckout
{
    public function __construct(private ShowCart $showCart) {}

    public function handle(CartSessionData $data): CheckoutViewResult
    {
        Gate::authorize('access', Cart::class);

        $cartResult = $this->showCart->handle($data);

        return new CheckoutViewResult(
            $cartResult->cart,
            $cartResult->cart->currency,
            ['stripe', 'bank_transfer', 'cod'],
            ['vendor', 'platform'],
            $data->user?->name,
            $data->user?->email,
            $cartResult->guestToken,
        );
    }
}
