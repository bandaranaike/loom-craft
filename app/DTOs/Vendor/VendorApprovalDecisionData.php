<?php

namespace App\DTOs\Vendor;

use App\Http\Requests\Admin\ApproveVendorRequest;
use App\Models\User;
use App\Models\Vendor;

class VendorApprovalDecisionData
{
    public function __construct(
        public User $user,
        public Vendor $vendor,
    ) {}

    public static function fromRequest(
        ApproveVendorRequest $request,
        Vendor $vendor,
    ): self {
        return new self($request->user(), $vendor);
    }
}
