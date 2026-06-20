<?php

namespace App\Support;

class PaymentMethods
{
    /**
     * @return list<string>
     */
    public static function enabled(): array
    {
        return array_keys(array_filter(
            self::configured(),
            static fn (bool $enabled): bool => $enabled,
        ));
    }

    /**
     * @return array<string, bool>
     */
    public static function configured(): array
    {
        /** @var array<string, mixed> $paymentMethods */
        $paymentMethods = config('commerce.payment_methods', []);

        return array_map(
            static fn (mixed $enabled): bool => filter_var($enabled, FILTER_VALIDATE_BOOL),
            $paymentMethods,
        );
    }

    public static function isEnabled(string $paymentMethod): bool
    {
        return self::configured()[$paymentMethod] ?? false;
    }
}
