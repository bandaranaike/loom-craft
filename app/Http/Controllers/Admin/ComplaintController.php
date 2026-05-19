<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComplaintRequest;
use App\Http\Requests\Admin\UpdateComplaintStatusRequest;
use App\Models\Complaint;
use App\Models\Order;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ComplaintController extends Controller
{
    public function __construct(
        private readonly FulfillmentStatusService $fulfillmentStatusService,
    ) {}

    public function store(StoreComplaintRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('updateStatus', $order);

        $this->fulfillmentStatusService->createComplaint(
            order: $order,
            actor: $request->user(),
            data: $request->validated(),
        );

        return back()->with('status', 'Complaint recorded.');
    }

    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validated();

        $this->fulfillmentStatusService->updateComplaintStatus(
            complaint: $complaint,
            nextStatus: $validated['complaint_status'],
            actor: $request->user(),
            reason: $validated['reason'] ?? null,
            note: $validated['note'] ?? null,
            resolutionType: $validated['resolution_type'] ?? null,
            resolutionNote: $validated['resolution_note'] ?? null,
            courierClaimReference: $validated['courier_claim_reference'] ?? null,
        );

        return back()->with('status', 'Complaint status updated.');
    }
}
