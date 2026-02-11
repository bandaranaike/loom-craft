<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function create(?User $user): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $order->user_id === $user->id;
    }

    public function viewOwn(User $user): bool
    {
        return in_array($user->role, ['customer', 'vendor', 'admin'], true);
    }

    public function viewVendorIndex(User $user): bool
    {
        return $user->role === 'vendor'
            && $user->vendor !== null
            && $user->vendor->status === 'approved';
    }

    public function viewGuest(?User $user, Order $order): bool
    {
        return $user === null && $order->user_id === null;
    }

    public function viewConfirmation(?User $user): bool
    {
        return true;
    }
}
