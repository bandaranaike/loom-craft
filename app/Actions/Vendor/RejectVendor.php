<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorRejectionData;
use App\DTOs\Vendor\VendorRejectionResult;
use Illuminate\Support\Facades\Gate;

class RejectVendor
{
    public function handle(VendorRejectionData $data): VendorRejectionResult
    {
        Gate::authorize('reject', $data->vendor);

        $data->vendor->forceFill([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => $data->user->id,
        ])->save();

        return new VendorRejectionResult($data->vendor, $data->reason);
    }
}
