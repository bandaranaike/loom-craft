<?php

namespace App\DTOs\Vendor;

use App\Http\Requests\Vendor\StoreVendorRegistrationRequest;
use App\Models\User;

class VendorRegistrationData
{
    public function __construct(
        public User $user,
        public string $displayName,
    ) {}

    public static function fromRequest(StoreVendorRegistrationRequest $request): self
    {
        return new self(
            $request->user(),
            $request->string('display_name')->toString(),
        );
    }
}
