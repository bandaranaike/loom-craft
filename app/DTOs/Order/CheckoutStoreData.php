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
        return self::fromArray(
            $request->validated(),
            $request->user(),
            $request->cookie('loomcraft_guest_token'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?User $user, ?string $guestToken): self
    {
        return new self(
            $user,
            $guestToken,
            self::stringOrNull($data, 'guest_name'),
            self::stringOrNull($data, 'guest_email'),
            self::requiredString($data, 'shipping_responsibility'),
            self::requiredString($data, 'payment_method'),
            Currency::fromString(self::requiredString($data, 'currency')),
            new OrderAddressData(
                self::requiredString($data, 'shipping_full_name'),
                self::requiredString($data, 'shipping_line1'),
                self::stringOrNull($data, 'shipping_line2'),
                self::requiredString($data, 'shipping_city'),
                self::stringOrNull($data, 'shipping_region'),
                self::stringOrNull($data, 'shipping_postal_code'),
                self::requiredString($data, 'shipping_country_code'),
                self::stringOrNull($data, 'shipping_phone'),
            ),
            new OrderAddressData(
                self::requiredString($data, 'billing_full_name'),
                self::requiredString($data, 'billing_line1'),
                self::stringOrNull($data, 'billing_line2'),
                self::requiredString($data, 'billing_city'),
                self::stringOrNull($data, 'billing_region'),
                self::stringOrNull($data, 'billing_postal_code'),
                self::requiredString($data, 'billing_country_code'),
                self::stringOrNull($data, 'billing_phone'),
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requiredString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function stringOrNull(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value)) {
            return null;
        }

        return $value === '' ? null : $value;
    }
}
