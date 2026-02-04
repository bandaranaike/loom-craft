<?php

namespace App\DTOs\Vendor;

use App\Models\User;

class VendorApprovalIndexData
{
    public function __construct(
        public User $user,
        public string $status,
        public ?string $search,
        public int $perPage,
    ) {}

    public static function forPending(User $user, ?string $search, int $perPage): self
    {
        return new self($user, 'pending', $search ?: null, $perPage);
    }
}
