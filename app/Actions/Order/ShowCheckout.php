<?php

namespace App\Actions\Order;

use App\Actions\Cart\ShowCart;
use App\DTOs\Cart\CartSessionData;
use App\DTOs\Order\CheckoutViewResult;
use App\Models\Cart;
use App\Models\OrderAddress;
use App\Models\User;
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
            ['stripe', 'paypal', 'paypal_card', 'bank_transfer', 'cod'],
            $data->user?->name,
            $data->user?->email,
            $this->resolveDefaultCountryCode($data->user),
            $cartResult->guestToken,
        );
    }

    private function resolveDefaultCountryCode(?User $user): string
    {
        if ($user !== null) {
            $previousCountryCode = OrderAddress::query()
                ->whereIn('type', ['shipping', 'billing'])
                ->whereHas('order', static fn ($query) => $query->where('user_id', $user->id))
                ->latest('id')
                ->value('country_code');

            if (is_string($previousCountryCode) && $previousCountryCode !== '') {
                return strtoupper($previousCountryCode);
            }
        }

        return strtoupper((string) config('commerce.default_country_code', 'LK'));
    }
}
