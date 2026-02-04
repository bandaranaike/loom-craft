<?php

namespace App\DTOs\Vendor;

use App\Models\Vendor;

class VendorApprovalDecisionResult
{
    public function __construct(
        public Vendor $vendor,
    ) {}
}
