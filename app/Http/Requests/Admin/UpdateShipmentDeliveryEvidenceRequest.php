<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateShipmentDeliveryEvidenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        $shipment = $this->route('shipment');

        return $order instanceof Order
            && $shipment instanceof Shipment
            && $shipment->order_id === $order->id
            && $this->user()?->role === 'admin'
            && Gate::allows('updateStatus', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'proof_reference' => ['nullable', 'string', 'max:255'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
