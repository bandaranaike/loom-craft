<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateShipmentTrackingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'carrier' => ['required', 'string', 'max:120'],
            'service_level' => ['nullable', 'string', 'max:120'],
            'tracking_number' => ['required', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'carrier.required' => 'Enter the courier or carrier name.',
            'tracking_number.required' => 'Enter the courier tracking number.',
        ];
    }
}
