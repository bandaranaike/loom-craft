<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\IndexVendorInquiriesRequest;
use App\Models\VendorContactSubmission;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function index(IndexVendorInquiriesRequest $request): Response
    {
        $vendor = $request->user()?->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $status = $request->string('status')->toString() ?: null;

        $inquiries = VendorContactSubmission::query()
            ->where('vendor_id', $vendor->id)
            ->when($status, fn ($query, string $selectedStatus) => $query->where('status', $selectedStatus))
            ->latest('submitted_at')
            ->get()
            ->map(fn (VendorContactSubmission $submission): array => [
                'id' => $submission->id,
                'name' => $submission->name,
                'email' => $submission->email,
                'phone' => $submission->phone,
                'subject' => $submission->subject,
                'message' => $submission->message,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->toDateTimeString(),
                'handled_at' => $submission->handled_at?->toDateTimeString(),
            ])
            ->values()
            ->all();

        return Inertia::render('vendor/inquiries/index', [
            'inquiries' => $inquiries,
            'selected_status' => $status,
            'status' => session('status'),
        ]);
    }
}
