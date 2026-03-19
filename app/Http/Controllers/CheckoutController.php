<?php

namespace App\Http\Controllers;

use App\Actions\Order\PlaceOrder;
use App\Actions\Order\ShowCheckout;
use App\DTOs\Cart\CartSessionData;
use App\DTOs\Order\CheckoutStoreData;
use App\Http\Requests\Order\StoreCheckoutRequest;
use App\Services\Payments\PayPalOrderService;
use App\Services\Payments\PayPalPaymentQuoteService;
use App\Services\Payments\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class CheckoutController extends Controller
{
    public function show(
        Request $request,
        ShowCheckout $action,
        PayPalPaymentQuoteService $payPalPaymentQuoteService,
        StripeCheckoutService $stripeCheckoutService,
    ): Response|RedirectResponse {
        $result = $action->handle(CartSessionData::fromRequest($request));

        if ($result->cart->itemCount === 0) {
            return redirect()
                ->route('cart.show')
                ->with('status', 'Your cart is empty.');
        }

        $paypalConfigured = app(PayPalOrderService::class)->isConfigured();
        $paypalQuote = null;
        $paypalUnavailableReason = null;

        if ($paypalConfigured) {
            try {
                $paypalQuote = $payPalPaymentQuoteService->quote($result->cart->subtotal)->toArray();
            } catch (\RuntimeException $exception) {
                $paypalUnavailableReason = $exception->getMessage();
            }
        }

        $response = Inertia::render('checkout', [
            ...$result->toArray(),
            'canRegister' => Features::enabled(Features::registration()),
            'paypal_configured' => $paypalConfigured,
            'paypal_client_id' => app(PayPalOrderService::class)->sdkClientId(),
            'paypal_quote' => $paypalQuote,
            'paypal_unavailable_reason' => $paypalUnavailableReason,
            'stripe_configured' => $stripeCheckoutService->isConfigured(),
        ]);

        if ($request->user() === null && $result->guestToken !== null) {
            Cookie::queue(cookie('loomcraft_guest_token', $result->guestToken, 60 * 24 * 30));
        }

        return $response;
    }

    public function store(
        StoreCheckoutRequest $request,
        PlaceOrder $action,
    ): RedirectResponse {
        if (in_array($request->string('payment_method')->toString(), ['stripe', 'paypal', 'paypal_card'], true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Use the selected payment provider to complete this checkout.',
            ]);
        }

        $result = $action->handle(CheckoutStoreData::fromRequest($request));

        if ($request->user() === null) {
            $request->session()->put('guest_order_id', $result->orderId);
        }

        $response = redirect()->route('orders.confirmation', ['order' => $result->publicOrderId]);

        if ($request->user() === null && $result->guestToken !== null) {
            $response->withCookie(cookie('loomcraft_guest_token', $result->guestToken, 60 * 24 * 30));
        }

        return $response;
    }
}
