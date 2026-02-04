<?php

namespace App\DTOs\Vendor;

use App\Models\Vendor;

class VendorRegistrationResult
{
    public function __construct(
        public Vendor $vendor,
    ) {}
}
