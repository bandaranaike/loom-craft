<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOfflineOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        if (! $order instanceof Order) {
            return false;
        }

        $payment = $order->payment;

        return $payment !== null
            && in_array($payment->method, ['bank_transfer', 'cod'], true)
            && $this->user()?->role === 'admin'
            && Gate::allows('manageOffline', $order);
    }

    public function rules(): array
    {
        $payment = $this->route('order')?->payment;

        return [
            'payment_status' => ['required', Rule::in(
                $payment === null
                    ? PaymentStatus::values()
                    : app(FulfillmentStatusService::class)->paymentStatusOptionsFor($payment)
            )],
            'cod_remitted_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'cod_remittance_reference' => ['nullable', 'string', 'max:120'],
            'cod_settlement_note' => ['nullable', 'string', 'max:1000'],
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

                $payment = $order->payment;

                if ($payment === null) {
                    return;
                }

                if ($payment->method === 'cod' && $this->validated('payment_status') === PaymentStatus::Paid->value) {
                    $remittedAmount = $this->validated('cod_remitted_amount');

                    if ($remittedAmount === null || $remittedAmount === '') {
                        $validator->errors()->add('cod_remitted_amount', 'Enter the COD remitted amount.');

                        return;
                    }

                    if (is_numeric($remittedAmount) && round((float) $remittedAmount, 2) !== round((float) $payment->amount, 2)) {
                        $validator->errors()->add('cod_remitted_amount', 'The COD remitted amount must match the order payment amount.');
                    }
                }

                if (! app(FulfillmentStatusService::class)->canTransitionPayment(
                    $order,
                    $payment,
                    $this->validated('payment_status'),
                    $this->user(),
                )) {
                    $validator->errors()->add('payment_status', 'Select a valid next payment status.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_status.required' => 'Select a payment status.',
            'payment_status.in' => 'Select a valid payment status.',
            'cod_remitted_amount.numeric' => 'Enter a valid COD remitted amount.',
            'cod_remitted_amount.decimal' => 'Enter the COD remitted amount with no more than two decimal places.',
        ];
    }
}
