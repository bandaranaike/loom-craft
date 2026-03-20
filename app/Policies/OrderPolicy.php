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
        return $order->user_id === $user->id;
    }

    public function viewOwn(User $user): bool
    {
        return in_array($user->role, ['customer', 'vendor', 'admin'], true);
    }

    public function viewDashboard(User $user): bool
    {
        return $user->id > 0;
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

    public function viewVendor(User $user, Order $order): bool
    {
        return $this->hasApprovedVendorProfile($user)
            && $this->vendorOwnsOrder($user, $order);
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $this->hasApprovedVendorProfile($user)
            && $this->vendorOwnsOrder($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }

    public function manageOffline(User $user, Order $order): bool
    {
        if (! in_array($order->payment?->method, ['bank_transfer', 'cod'], true)) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return $this->hasApprovedVendorProfile($user)
            && $this->vendorOwnsOrder($user, $order);
    }

    private function hasApprovedVendorProfile(User $user): bool
    {
        return $user->role === 'vendor'
            && $user->vendor !== null
            && $user->vendor->status === 'approved';
    }

    private function vendorOwnsOrder(User $user, Order $order): bool
    {
        $vendor = $user->vendor;

        if ($vendor === null) {
            return false;
        }

        return $order->items()
            ->where('vendor_id', $vendor->id)
            ->exists();
    }
}
