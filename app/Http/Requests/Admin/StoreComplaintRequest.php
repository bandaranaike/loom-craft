<?php

namespace App\Http\Requests\Admin;

use App\Enums\ComplaintCategory;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
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
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'shipment_id' => ['nullable', 'integer', Rule::exists('shipments', 'id')],
            'order_return_id' => ['nullable', 'integer', Rule::exists('order_returns', 'id')],
            'payment_id' => ['nullable', 'integer', Rule::exists('payments', 'id')],
            'category' => ['required', 'string', Rule::in(ComplaintCategory::values())],
            'severity' => ['nullable', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'resolution_type' => ['nullable', 'string', 'max:64'],
            'courier_claim_reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $order = $this->route('order');

                if (! $order instanceof Order || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $shipmentId = $this->integer('shipment_id');
                $returnId = $this->integer('order_return_id');
                $paymentId = $this->integer('payment_id');

                if ($shipmentId > 0 && ! Shipment::query()->where('order_id', $order->id)->whereKey($shipmentId)->exists()) {
                    $validator->errors()->add('shipment_id', 'Select a shipment that belongs to this order.');
                }

                if ($returnId > 0 && ! OrderReturn::query()->where('order_id', $order->id)->whereKey($returnId)->exists()) {
                    $validator->errors()->add('order_return_id', 'Select a return that belongs to this order.');
                }

                if ($paymentId > 0 && ! Payment::query()->where('order_id', $order->id)->whereKey($paymentId)->exists()) {
                    $validator->errors()->add('payment_id', 'Select a payment that belongs to this order.');
                }
            },
        ];
    }
}
