<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Vendor\ApproveVendor;
use App\Actions\Vendor\ListPendingVendors;
use App\Actions\Vendor\RejectVendor;
use App\DTOs\Vendor\VendorApprovalDecisionData;
use App\DTOs\Vendor\VendorApprovalIndexData;
use App\DTOs\Vendor\VendorRejectionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveVendorRequest;
use App\Http\Requests\Admin\RejectVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorApprovalController extends Controller
{
    public function pending(Request $request, ListPendingVendors $action): Response
    {
        $result = $action->handle(
            VendorApprovalIndexData::forPending($request->user())
        );

        return Inertia::render('admin/vendors/pending', [
            ...$result->toArray(),
            'status' => session('status'),
        ]);
    }

    public function approve(
        ApproveVendorRequest $request,
        Vendor $vendor,
        ApproveVendor $action,
    ): RedirectResponse {
        $action->handle(VendorApprovalDecisionData::fromRequest($request, $vendor));

        return redirect()
            ->route('admin.vendors.pending')
            ->with('status', 'Vendor approved successfully.');
    }

    public function reject(
        RejectVendorRequest $request,
        Vendor $vendor,
        RejectVendor $action,
    ): RedirectResponse {
        $result = $action->handle(VendorRejectionData::fromRequest($request, $vendor));

        return redirect()
            ->route('admin.vendors.pending')
            ->with('status', "Vendor rejected: {$result->reason}");
    }
}
