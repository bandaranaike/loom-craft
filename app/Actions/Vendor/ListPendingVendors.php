<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorApprovalIndexData;
use App\DTOs\Vendor\VendorApprovalIndexResult;
use App\DTOs\Vendor\VendorApprovalListItem;
use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;

class ListPendingVendors
{
    public function handle(VendorApprovalIndexData $data): VendorApprovalIndexResult
    {
        Gate::authorize('viewAny', Vendor::class);

        $vendors = Vendor::query()
            ->with('user')
            ->where('status', $data->status)
            ->latest()
            ->get()
            ->map(static fn (Vendor $vendor): VendorApprovalListItem => new VendorApprovalListItem(
                $vendor->id,
                $vendor->display_name,
                $vendor->location,
                $vendor->status,
                $vendor->created_at?->toDateTimeString(),
                $vendor->user?->name ?? 'Unknown',
                $vendor->user?->email ?? 'Unknown',
            ))
            ->all();

        return new VendorApprovalIndexResult($vendors);
    }
}
