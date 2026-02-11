<?php

namespace App\DTOs\Order;

use App\Http\Requests\Order\StoreCheckoutRequest;
use App\Models\User;
use App\ValueObjects\Currency;

class CheckoutStoreData
{
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
        public ?string $guestName,
        public ?string $guestEmail,
        public string $shippingResponsibility,
        public string $paymentMethod,
        public Currency $currency,
        public OrderAddressData $shippingAddress,
        public OrderAddressData $billingAddress,
    ) {}

    public static function fromRequest(StoreCheckoutRequest $request): self
    {
        return new self(
            $request->user(),
            $request->cookie('loomcraft_guest_token'),
            $request->string('guest_name')->toString() ?: null,
            $request->string('guest_email')->toString() ?: null,
            $request->string('shipping_responsibility')->toString(),
            $request->string('payment_method')->toString(),
            Currency::fromString($request->string('currency')->toString()),
            new OrderAddressData(
                $request->string('shipping_full_name')->toString(),
                $request->string('shipping_line1')->toString(),
                $request->string('shipping_line2')->toString() ?: null,
                $request->string('shipping_city')->toString(),
                $request->string('shipping_region')->toString() ?: null,
                $request->string('shipping_postal_code')->toString() ?: null,
                $request->string('shipping_country_code')->toString(),
                $request->string('shipping_phone')->toString() ?: null,
            ),
            new OrderAddressData(
                $request->string('billing_full_name')->toString(),
                $request->string('billing_line1')->toString(),
                $request->string('billing_line2')->toString() ?: null,
                $request->string('billing_city')->toString(),
                $request->string('billing_region')->toString() ?: null,
                $request->string('billing_postal_code')->toString() ?: null,
                $request->string('billing_country_code')->toString(),
                $request->string('billing_phone')->toString() ?: null,
            ),
        );
    }
}
