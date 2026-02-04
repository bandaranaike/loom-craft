<?php

namespace App\DTOs\Vendor;

use App\Http\Requests\Vendor\StoreVendorRegistrationRequest;
use App\Models\User;

class VendorRegistrationData
{
    public function __construct(
        public User $user,
        public string $displayName,
        public ?string $bio,
        public ?string $location,
    ) {}

    public static function fromRequest(StoreVendorRegistrationRequest $request): self
    {
        return new self(
            $request->user(),
            $request->string('display_name')->toString(),
            $request->string('bio')->toString() ?: null,
            $request->string('location')->toString() ?: null,
        );
    }
}
