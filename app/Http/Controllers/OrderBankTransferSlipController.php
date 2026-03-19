<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreBankTransferSlipRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class OrderBankTransferSlipController extends Controller
{
    public function store(StoreBankTransferSlipRequest $request, Order $order): RedirectResponse
    {
        $payment = $order->payment;

        if ($payment === null || $payment->method !== 'bank_transfer') {
            abort(404);
        }

        if (is_string($payment->bank_transfer_slip_path) && $payment->bank_transfer_slip_path !== '') {
            Storage::disk('public')->delete($payment->bank_transfer_slip_path);
        }

        $file = $request->file('slip');

        if ($file === null || ! $file->isValid()) {
            return back()->withErrors([
                'slip' => 'Upload a valid bank transfer slip.',
            ]);
        }

        $payment->update([
            'bank_transfer_slip_path' => $file->store('bank-transfer-slips', 'public'),
            'bank_transfer_slip_original_name' => $file->getClientOriginalName(),
            'bank_transfer_slip_mime_type' => $file->getClientMimeType(),
            'bank_transfer_slip_uploaded_at' => now(),
        ]);

        return back()->with('status', 'Bank transfer slip uploaded successfully.');
    }
}
