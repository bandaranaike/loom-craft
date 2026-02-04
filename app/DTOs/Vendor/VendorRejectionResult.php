<?php

namespace App\DTOs\Vendor;

use App\Models\Vendor;

class VendorRejectionResult
{
    public function __construct(
        public Vendor $vendor,
        public string $reason,
    ) {}
}
