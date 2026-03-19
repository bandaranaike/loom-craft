<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;

class StoreBankTransferSlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        if (! $order instanceof Order) {
            return false;
        }

        $payment = $order->payment;

        if ($payment === null || $payment->method !== 'bank_transfer') {
            return false;
        }

        if ($this->user() !== null) {
            return Gate::allows('view', $order);
        }

        return Gate::allows('viewGuest', $order)
            && $this->session()->get('guest_order_id') === $order->id;
    }

    public function rules(): array
    {
        return [
            'slip' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp'])->max(10 * 1024),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slip.required' => 'Upload the final bank transfer slip.',
            'slip.max' => 'The bank transfer slip must be 10 MB or smaller.',
        ];
    }
}
