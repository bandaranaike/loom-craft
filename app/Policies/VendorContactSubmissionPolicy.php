<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorContactSubmission;

class VendorContactSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, VendorContactSubmission $vendorContactSubmission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'vendor'
            && $user->vendor !== null
            && $user->vendor->id === $vendorContactSubmission->vendor_id;
    }

    public function approve(User $user, VendorContactSubmission $vendorContactSubmission): bool
    {
        return $user->role === 'admin' && $vendorContactSubmission->status === 'pending';
    }

    public function reject(User $user, VendorContactSubmission $vendorContactSubmission): bool
    {
        return $user->role === 'admin' && $vendorContactSubmission->status === 'pending';
    }
}
