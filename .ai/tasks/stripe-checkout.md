# Task: Stripe Checkout

## Goal
Add Stripe Checkout as an instant payment method in the existing LoomCraft checkout flow so customers can complete card payments through Stripe and return to the standard order confirmation path.

## Current Context
- The storefront already has a working checkout page at `/checkout` backed by `CheckoutController` and `PlaceOrder`.
- Instant payment methods currently include `paypal` and `paypal_card`, while `stripe` is only a label in the checkout form and falls through the generic non-provider flow.
- Order placement already supports instant-paid methods by marking `stripe`, `paypal`, and `paypal_card` as paid.
- The current Stripe integration is incomplete:
  - there is no Stripe service class
  - there is no Stripe checkout controller
  - there are no Stripe success or cancel callbacks
  - there is no Stripe SDK or Cashier package in `composer.json`
- PayPal uses a dedicated controller plus session-backed pending checkout payloads. Stripe should follow a similarly explicit server-side flow instead of relying on an untracked frontend-only redirect.

## Problem To Solve
1. Customers can choose Stripe during checkout, but that option does not currently create a real Stripe payment.
2. Orders must only be created after Stripe confirms payment success.
3. The checkout payload, cart integrity, and guest checkout behavior must remain consistent with the existing order flow.
4. Payment records must persist the Stripe session or payment reference for later support and reconciliation.

## Recommended Direction
1. Use hosted Stripe Checkout first, not Elements.
2. Keep the current application checkout form as the place where customer address and guest information are collected.
3. After validation, create a Stripe Checkout Session on the server and redirect the customer to Stripe.
4. Store the validated checkout payload in session using a Stripe-specific pending checkout key, similar to the PayPal flow.
5. Finalize the order only after Stripe redirects back with a successful session identifier and the session is verified server-side.
6. Persist the Stripe session ID or payment intent ID as the payment provider reference.

## Scope
### In Scope
1. Add backend Stripe configuration.
2. Add a Stripe checkout service class.
3. Add controller endpoints for:
   - creating a Stripe Checkout Session
   - handling successful Stripe return
   - handling cancelled Stripe return
4. Update checkout UI behavior so selecting `stripe` starts the Stripe redirect flow instead of placing the order directly.
5. Add automated feature tests for the Stripe checkout flow.

### Out Of Scope For This Task
1. Stripe Connect marketplace split payments.
2. Stripe Elements or embedded card fields.
3. Saved payment methods.
4. Subscription billing.
5. Webhook-driven fulfillment beyond what is required for robust hosted Checkout verification.

## Backend Work
1. Configuration
- Add `STRIPE_KEY`, `STRIPE_SECRET`, and any other required Stripe settings to `config/services.php`.
- Decide whether to include a `STRIPE_WEBHOOK_SECRET` now or in a follow-up task.

2. Stripe service
- Create a dedicated payment service under `app/Services/Payments/`.
- Responsibilities:
  - verify configuration exists
  - create hosted Checkout Sessions
  - fetch and verify a Checkout Session by ID on success return

3. Checkout flow
- Add a dedicated Stripe controller similar in clarity to `CheckoutPayPalController`.
- Validate the same checkout payload already used by `StoreCheckoutRequest`.
- Build the Stripe session from the current cart subtotal and checkout metadata.
- Store pending Stripe checkout data in session before redirecting away.
- On successful return:
  - verify the Stripe session server-side
  - ensure payment status is successful
  - rebuild `CheckoutStoreData` from the pending session payload
  - call `PlaceOrder` with the Stripe provider reference
- On cancellation:
  - return to the checkout page with a useful status message

4. Integrity rules
- The checkout currency should remain aligned with the current canonical app flow.
- The final order amount must match the cart amount represented in the Stripe session.
- If the pending Stripe session payload cannot be matched, fail safely and return the customer to checkout.

## Frontend Work
1. Update `resources/js/pages/checkout.tsx` so `payment_method === 'stripe'` no longer posts to the generic order placement endpoint.
2. Instead, submit to a Stripe checkout-session creation endpoint and redirect the browser to the returned Stripe URL.
3. Reuse the same validated form payload currently sent for PayPal order creation.
4. Preserve existing validation error rendering and guest checkout behavior.

## Data / Persistence
1. Reuse the existing `payments.provider_reference` field for the Stripe session ID or payment intent ID.
2. Keep `payments.method = stripe`.
3. Keep `payments.status = paid` only after Stripe success is verified.

## Testing Requirements
1. Creating a Stripe checkout session requires a valid checkout payload.
2. Stripe checkout creation fails gracefully when Stripe is not configured.
3. Stripe checkout success places the order and stores the Stripe provider reference.
4. Stripe checkout success for guests preserves guest order confirmation behavior.
5. Stripe checkout cancellation returns the customer to checkout with a status message.
6. A mismatched or missing pending Stripe session is rejected safely.

## Acceptance Criteria
1. Selecting Stripe at checkout redirects the customer to a real Stripe Checkout page.
2. Orders are not placed before Stripe payment success is confirmed.
3. Successful Stripe payments land on the normal order confirmation flow.
4. Stripe cancellations return to checkout cleanly.
5. Payment records store a Stripe provider reference.
6. The flow is covered by automated tests.

## Open Decisions
1. Should this use plain Stripe PHP SDK or Laravel Cashier Stripe?
2. Do you want webhook verification in the first implementation, or is hosted Checkout return verification sufficient for phase one?
3. Is the Stripe account intended for direct platform charges only, or do you need future vendor payout / Connect support?
