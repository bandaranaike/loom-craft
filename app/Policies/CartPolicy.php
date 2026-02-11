<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    public function access(?User $user): bool
    {
        return true;
    }

    public function manage(?User $user, Cart $cart, ?string $guestToken = null): bool
    {
        if ($user !== null) {
            return $cart->user_id === $user->id;
        }

        if ($guestToken === null || $cart->guest_token === null) {
            return false;
        }

        return hash_equals($cart->guest_token, $guestToken);
    }
}
