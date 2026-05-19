<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderReturnRequest extends FormRequest
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
            'shipment_id' => ['nullable', 'integer', Rule::exists('shipments', 'id')],
            'reason' => ['required', 'string', 'max:64'],
            'customer_note' => ['nullable', 'string', 'max:5000'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'distinct', Rule::exists('order_items', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.condition' => ['nullable', 'string', 'max:64'],
            'items.*.resolution' => ['nullable', 'string', 'max:64'],
            'items.*.note' => ['nullable', 'string', 'max:2000'],
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

                if ($shipmentId > 0 && ! Shipment::query()->where('order_id', $order->id)->whereKey($shipmentId)->exists()) {
                    $validator->errors()->add('shipment_id', 'Select a shipment that belongs to this order.');
                }

                foreach ($this->input('items', []) as $index => $item) {
                    $orderItem = OrderItem::query()
                        ->where('order_id', $order->id)
                        ->find($item['order_item_id'] ?? null);

                    if ($orderItem === null) {
                        $validator->errors()->add("items.{$index}.order_item_id", 'Select an item that belongs to this order.');

                        continue;
                    }

                    if (($item['quantity'] ?? 0) > $orderItem->quantity) {
                        $validator->errors()->add("items.{$index}.quantity", 'Return quantity cannot exceed the ordered quantity.');
                    }
                }
            },
        ];
    }
}
