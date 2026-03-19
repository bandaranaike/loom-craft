<?php

namespace App\Http\Controllers;

use App\Actions\Order\PlaceOrder;
use App\Actions\Order\ShowCheckout;
use App\DTOs\Cart\CartSessionData;
use App\DTOs\Order\CheckoutStoreData;
use App\Http\Requests\Order\StoreCheckoutRequest;
use App\Services\Payments\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutStripeController extends Controller
{
    public function create(
        StoreCheckoutRequest $request,
        ShowCheckout $showCheckout,
        StripeCheckoutService $stripeCheckoutService,
    ): JsonResponse {
        if (! $stripeCheckoutService->isConfigured()) {
            return response()->json([
                'message' => 'Stripe is not configured yet.',
            ], 503);
        }

        $validated = $request->validated();

        if (($validated['payment_method'] ?? null) !== 'stripe') {
            throw ValidationException::withMessages([
                'payment_method' => 'Stripe is required for this checkout action.',
            ]);
        }

        $checkoutResult = $showCheckout->handle(CartSessionData::fromRequest($request));
        $checkoutCurrency = strtoupper($checkoutResult->currency);

        if (($validated['currency'] ?? null) !== $checkoutCurrency) {
            throw ValidationException::withMessages([
                'currency' => 'Cart currency must match checkout currency.',
            ]);
        }

        $checkoutSession = $stripeCheckoutService->createCheckoutSession(
            $request->user(),
            $checkoutResult,
            route('checkout.stripe.approved').'?session_id={CHECKOUT_SESSION_ID}',
            route('checkout.stripe.cancelled'),
            $validated['guest_email'] ?? null,
        );

        $this->storePendingCheckout(
            $request,
            $checkoutSession->id,
            $validated,
            $checkoutResult->cart->subtotal,
            $checkoutCurrency,
        );

        return response()->json([
            'checkout_url' => $checkoutSession->url,
            'session_id' => $checkoutSession->id,
        ]);
    }

    public function approved(
        Request $request,
        PlaceOrder $placeOrder,
        StripeCheckoutService $stripeCheckoutService,
    ): RedirectResponse {
        $stripeSessionId = $request->string('session_id')->toString();

        if ($stripeSessionId === '') {
            return redirect()
                ->route('checkout.show')
                ->with('status', 'Stripe did not return a checkout session.');
        }

        $pendingCheckout = $this->pendingCheckoutPayload($request, $stripeSessionId);

        if ($pendingCheckout === null) {
            return redirect()
                ->route('checkout.show')
                ->with('status', 'Unable to match this Stripe checkout session. Please try again.');
        }

        try {
            $checkoutSession = $stripeCheckoutService->retrieveCheckoutSession($stripeSessionId);

            if ($checkoutSession->payment_status !== 'paid') {
                throw ValidationException::withMessages([
                    'payment_method' => 'Stripe payment was not completed.',
                ]);
            }

            $amountTotal = $stripeCheckoutService->normalizeAmountTotal($checkoutSession->amount_total);
            $currency = strtoupper((string) $checkoutSession->currency);

            if ($amountTotal !== $pendingCheckout['subtotal'] || $currency !== $pendingCheckout['currency']) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart changed during Stripe checkout. Please review the latest prices and try again.',
                ]);
            }

            $checkoutData = CheckoutStoreData::fromArray(
                $pendingCheckout['data'],
                $request->user(),
                $pendingCheckout['guest_token'],
            );
            $result = $placeOrder->handle(
                $checkoutData,
                is_string($checkoutSession->payment_intent) ? $checkoutSession->payment_intent : $checkoutSession->id,
            );
        } catch (Throwable) {
            return redirect()
                ->route('checkout.show')
                ->with('status', 'Stripe payment could not be completed. Please try again.');
        }

        $this->clearPendingCheckout($request, $stripeSessionId);

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
            ->with('status', 'Stripe checkout was cancelled.');
    }

    /**
     * @return array{data: array<string, mixed>, subtotal: string, currency: string, guest_token: ?string, created_at: int}|null
     */
    private function pendingCheckoutPayload(Request $request, string $stripeSessionId): ?array
    {
        $pendingSessions = $request->session()->get('checkout.stripe.pending', []);

        if (! is_array($pendingSessions)) {
            return null;
        }

        $payload = $pendingSessions[$stripeSessionId] ?? null;

        if (! is_array($payload)) {
            return null;
        }

        $data = $payload['data'] ?? null;
        $subtotal = $payload['subtotal'] ?? null;
        $currency = $payload['currency'] ?? null;
        $guestToken = $payload['guest_token'] ?? null;
        $createdAt = $payload['created_at'] ?? null;

        if (! is_array($data) || ! is_string($subtotal) || ! is_string($currency) || ! is_int($createdAt)) {
            return null;
        }

        if ($guestToken !== null && ! is_string($guestToken)) {
            return null;
        }

        return [
            'data' => $data,
            'subtotal' => $subtotal,
            'currency' => $currency,
            'guest_token' => $guestToken,
            'created_at' => $createdAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function storePendingCheckout(
        Request $request,
        string $stripeSessionId,
        array $validated,
        string $subtotal,
        string $currency,
    ): void {
        $pendingCheckout = $request->session()->get('checkout.stripe.pending', []);
        $pendingCheckout[$stripeSessionId] = [
            'data' => $validated,
            'subtotal' => $subtotal,
            'currency' => $currency,
            'guest_token' => $request->cookie('loomcraft_guest_token'),
            'created_at' => now()->timestamp,
        ];
        $request->session()->put('checkout.stripe.pending', $pendingCheckout);
    }

    private function clearPendingCheckout(Request $request, string $stripeSessionId): void
    {
        $pendingSessions = $request->session()->get('checkout.stripe.pending', []);
        unset($pendingSessions[$stripeSessionId]);
        $request->session()->put('checkout.stripe.pending', $pendingSessions);
    }
}
