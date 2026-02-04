<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorApprovalDecisionData;
use App\DTOs\Vendor\VendorApprovalDecisionResult;
use Illuminate\Support\Facades\Gate;

class ApproveVendor
{
    public function handle(VendorApprovalDecisionData $data): VendorApprovalDecisionResult
    {
        Gate::authorize('approve', $data->vendor);

        $data->vendor->user()->update([
            'role' => 'vendor',
        ]);

        $data->vendor->forceFill([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $data->user->id,
        ])->save();

        return new VendorApprovalDecisionResult($data->vendor);
    }
}
