<?php

namespace App\DTOs\Vendor;

use App\Http\Requests\Admin\RejectVendorRequest;
use App\Models\User;
use App\Models\Vendor;

class VendorRejectionData
{
    public function __construct(
        public User $user,
        public Vendor $vendor,
        public string $reason,
    ) {}

    public static function fromRequest(
        RejectVendorRequest $request,
        Vendor $vendor,
    ): self {
        return new self(
            $request->user(),
            $vendor,
            $request->string('reason')->toString(),
        );
    }
}
