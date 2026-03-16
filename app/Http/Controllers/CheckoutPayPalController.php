<?php

namespace App\Http\Controllers;

use App\Actions\Order\PlaceOrder;
use App\Actions\Order\ShowCheckout;
use App\DTOs\Cart\CartSessionData;
use App\DTOs\Order\CheckoutStoreData;
use App\DTOs\Payment\PayPalPaymentQuote;
use App\Http\Requests\Order\StoreCheckoutRequest;
use App\Services\Payments\PayPalOrderService;
use App\Services\Payments\PayPalPaymentQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CheckoutPayPalController extends Controller
{
    public function create(
        StoreCheckoutRequest $request,
        ShowCheckout $showCheckout,
        PayPalPaymentQuoteService $payPalPaymentQuoteService,
        PayPalOrderService $payPalOrderService,
    ): JsonResponse {
        if (! $payPalOrderService->isConfigured()) {
            return response()->json([
                'message' => 'PayPal is not configured yet.',
            ], 503);
        }

        $validated = $request->validated();

        if (($validated['payment_method'] ?? null) !== 'paypal') {
            throw ValidationException::withMessages([
                'payment_method' => 'PayPal is required for this checkout action.',
            ]);
        }

        $checkoutResult = $showCheckout->handle(CartSessionData::fromRequest($request));
        $checkoutCurrency = strtoupper($checkoutResult->currency);

        if (($validated['currency'] ?? null) !== $checkoutCurrency) {
            throw ValidationException::withMessages([
                'currency' => 'Cart currency must match checkout currency.',
            ]);
        }

        try {
            $quote = $payPalPaymentQuoteService->quote($checkoutResult->cart->subtotal);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'payment_method' => $exception->getMessage(),
            ]);
        }

        $paypalOrder = $payPalOrderService->createOrder(
            $quote->convertedCurrency,
            $quote->convertedAmount,
            route('checkout.paypal.approved'),
            route('checkout.paypal.cancelled'),
        );

        $pendingCheckout = $request->session()->get('checkout.paypal.pending', []);
        $pendingCheckout[$paypalOrder['order_id']] = [
            'data' => $validated,
            'quote' => $quote->toArray(),
            'guest_token' => $request->cookie('loomcraft_guest_token'),
            'created_at' => now()->timestamp,
        ];
        $request->session()->put('checkout.paypal.pending', $pendingCheckout);

        return response()->json([
            'order_id' => $paypalOrder['order_id'],
            'approve_url' => $paypalOrder['approve_url'],
        ]);
    }

    public function approved(
        Request $request,
        PlaceOrder $placeOrder,
        PayPalOrderService $payPalOrderService,
    ): RedirectResponse {
        $paypalOrderId = $request->string('token')->toString();

        if ($paypalOrderId === '') {
            return redirect()
                ->route('checkout.show')
                ->with('status', 'PayPal did not return an order token.');
        }

        $pendingCheckout = $this->pendingCheckoutPayload($request, $paypalOrderId);

        if ($pendingCheckout === null) {
            return redirect()
                ->route('checkout.show')
                ->with('status', 'Unable to match this PayPal checkout session. Please try again.');
        }

        try {
            $captureResult = $payPalOrderService->captureOrder($paypalOrderId);

            if ($captureResult['status'] !== 'COMPLETED') {
                throw new RuntimeException('PayPal payment was not completed.');
            }

            $checkoutData = CheckoutStoreData::fromArray(
                $pendingCheckout['data'],
                $request->user(),
                $pendingCheckout['guest_token'],
            );
            $result = $placeOrder->handle(
                $checkoutData,
                $captureResult['capture_id'],
                PayPalPaymentQuote::fromArray($pendingCheckout['quote']),
            );
        } catch (Throwable) {
            return redirect()
                ->route('checkout.show')
                ->with('status', 'PayPal payment could not be completed. Please try again.');
        }

        $pendingOrders = $request->session()->get('checkout.paypal.pending', []);
        unset($pendingOrders[$paypalOrderId]);
        $request->session()->put('checkout.paypal.pending', $pendingOrders);

        if ($request->user() === null) {
            $request->session()->put('guest_order_id', $result->orderId);
        }

        $response = redirect()->route('orders.confirmation', ['order' => $result->orderId]);

        if ($request->user() === null && $result->guestToken !== null) {
            $response->withCookie(cookie('loomcraft_guest_token', $result->guestToken, 60 * 24 * 30));
        }

        return $response;
    }

    public function cancelled(): RedirectResponse
    {
        return redirect()
            ->route('checkout.show')
            ->with('status', 'PayPal checkout was cancelled.');
    }

    /**
     * @return array{data: array<string, mixed>, quote: array<string, string>, guest_token: ?string, created_at: int}|null
     */
    private function pendingCheckoutPayload(Request $request, string $paypalOrderId): ?array
    {
        $pendingOrders = $request->session()->get('checkout.paypal.pending', []);

        if (! is_array($pendingOrders)) {
            return null;
        }

        $payload = $pendingOrders[$paypalOrderId] ?? null;

        if (! is_array($payload)) {
            return null;
        }

        $data = $payload['data'] ?? null;
        $quote = $payload['quote'] ?? null;
        $guestToken = $payload['guest_token'] ?? null;
        $createdAt = $payload['created_at'] ?? null;

        if (! is_array($data) || ! is_array($quote) || ! is_int($createdAt)) {
            return null;
        }

        if ($guestToken !== null && ! is_string($guestToken)) {
            return null;
        }

        return [
            'data' => $data,
            'quote' => $quote,
            'guest_token' => $guestToken,
            'created_at' => $createdAt,
        ];
    }
}
