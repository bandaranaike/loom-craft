<?php

namespace App\DTOs\Vendor;

use App\Models\User;

class VendorApprovalIndexData
{
    public function __construct(
        public User $user,
        public string $status,
    ) {}

    public static function forPending(User $user): self
    {
        return new self($user, 'pending');
    }
}
