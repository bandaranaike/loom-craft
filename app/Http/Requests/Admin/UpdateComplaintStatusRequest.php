<?php

namespace App\Http\Requests\Admin;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateComplaintStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        return $complaint instanceof Complaint
            && $complaint->order !== null
            && $this->user()?->role === 'admin'
            && Gate::allows('updateStatus', $complaint->order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'complaint_status' => ['required', 'string', Rule::in(ComplaintStatus::values())],
            'reason' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:5000'],
            'resolution_type' => ['nullable', 'string', 'max:64'],
            'resolution_note' => ['nullable', 'string', 'max:5000'],
            'courier_claim_reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $complaint = $this->route('complaint');

                if (! $complaint instanceof Complaint || $validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! app(FulfillmentStatusService::class)->canTransitionComplaint(
                    $complaint,
                    $this->validated('complaint_status'),
                    $this->user(),
                )) {
                    $validator->errors()->add('complaint_status', 'Select a valid next complaint status.');
                }
            },
        ];
    }
}
