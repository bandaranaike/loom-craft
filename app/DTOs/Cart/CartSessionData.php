<?php

namespace App\DTOs\Cart;

use App\Models\User;
use App\ValueObjects\Currency;
use Illuminate\Http\Request;

class CartSessionData
{
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
        public Currency $currency,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $currency = $request->string('currency')->toString();

        return new self(
            $request->user(),
            $request->cookie('loomcraft_guest_token'),
            Currency::fromString($currency !== '' ? $currency : 'USD'),
        );
    }
}
