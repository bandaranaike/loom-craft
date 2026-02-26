<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModerateVendorInquiryRequest;
use App\Models\VendorContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VendorInquiryController extends Controller
{
    public function pending(Request $request): Response
    {
        Gate::authorize('viewAny', VendorContactSubmission::class);

        $inquiries = VendorContactSubmission::query()
            ->with(['vendor'])
            ->where('status', 'pending')
            ->latest('submitted_at')
            ->get()
            ->map(function (VendorContactSubmission $submission): array {
                return [
                    'id' => $submission->id,
                    'vendor_id' => $submission->vendor_id,
                    'vendor_name' => $submission->vendor?->display_name ?? 'Unknown vendor',
                    'vendor_slug' => $submission->vendor?->slug,
                    'name' => $submission->name,
                    'email' => $submission->email,
                    'phone' => $submission->phone,
                    'subject' => $submission->subject,
                    'message' => $submission->message,
                    'submitted_at' => $submission->submitted_at?->toDateTimeString(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('admin/vendor-inquiries/pending', [
            'inquiries' => $inquiries,
            'status' => session('status'),
        ]);
    }

    public function approve(
        ModerateVendorInquiryRequest $request,
        VendorContactSubmission $inquiry,
    ): RedirectResponse {
        Gate::authorize('approve', $inquiry);

        $inquiry->forceFill([
            'status' => 'approved',
            'handled_by' => $request->user()?->id,
            'handled_at' => now(),
        ])->save();

        return redirect()
            ->route('admin.vendor-inquiries.pending')
            ->with('status', 'Inquiry approved successfully.');
    }

    public function reject(
        ModerateVendorInquiryRequest $request,
        VendorContactSubmission $inquiry,
    ): RedirectResponse {
        Gate::authorize('reject', $inquiry);

        $inquiry->forceFill([
            'status' => 'rejected',
            'handled_by' => $request->user()?->id,
            'handled_at' => now(),
        ])->save();

        return redirect()
            ->route('admin.vendor-inquiries.pending')
            ->with('status', 'Inquiry rejected successfully.');
    }
}
