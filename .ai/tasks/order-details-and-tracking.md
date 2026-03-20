# Task: Order Details And Tracking

## Status

- Implementation completed on 2026-03-19
- Focused Pest coverage re-run successfully on 2026-03-20 after enabling the SQLite PDO driver

## Goal

Upgrade the order details experience so each order has a durable platform-generated public identifier, a proper order
details page, accurate currency display, and a visual status timeline that reflects the real lifecycle of the order.

## Final Product Decisions

1. Public order identifiers use a platform-generated opaque token in the form `ORD-` plus a long cryptographically random uppercase string.
2. Canceled orders use a distinct canceled summary card instead of remaining on the normal progress timeline.
3. The order details page uses the opaque public identifier as the lookup key, but access is now limited to:
   - the authenticated order owner
   - the guest session associated with the order
4. The normal progress lifecycle now includes a dedicated `shipped` step before completion.

## Implementation Summary

### Completed Backend Work

1. Added `orders.public_id` with unique indexing and backfill migration for existing orders.
2. Orders now generate immutable public identifiers automatically on create.
3. Customer-facing order routes now resolve by `{order:public_id}`.
4. Checkout, Stripe, and PayPal completion redirects now use the public order identifier.
5. Order DTOs now expose:
   - `public_id`
   - order currency
   - payment currency and original payment currency
   - centralized progress payload
   - payment-proof upload capability flag
6. The order progress lifecycle now includes a `shipped` step.
7. Order detail access is now owner/session restricted instead of generally public.

### Completed Frontend Work

1. The main order tracking page now uses the public-facing site layout while still enforcing owner/session access.
2. The order pages now show the public order identifier prominently.
3. Added a visual order progress component for normal order flow.
4. Added a distinct canceled summary card.
5. Updated order summaries to show payment currency separately when it differs from the order currency.
6. Updated order history and dashboard links to open the public tracking page via the public identifier.
7. Updated the progress UI layout so the five-step lifecycle remains readable on mobile.

## Verification Notes

### Completed Checks

1. `php artisan wayfinder:generate --with-form --no-interaction`
2. `vendor/bin/pint --dirty --format agent`
3. `npm run types`
4. `npm run build`
5. `php -l` on the changed PHP files
6. `php artisan test --compact tests/Feature/OrderTrackingTest.php`

## Files Added Or Updated

1. `database/migrations/2026_03_19_150249_add_public_id_to_orders_table.php`
2. `app/Models/Order.php`
3. `app/Actions/Order/BuildOrderProgress.php`
4. `app/Actions/Order/ShowOrder.php`
5. `app/Actions/Order/ShowOrderConfirmation.php`
6. `app/Actions/Order/ListOrders.php`
7. `app/Actions/Order/ListDashboardOrderHistories.php`
8. `app/DTOs/Order/OrderSummaryResult.php`
9. `app/DTOs/Order/OrderListItem.php`
10. `app/DTOs/Order/OrderPlacementResult.php`
11. `resources/js/components/order-progress.tsx`
12. `resources/js/pages/orders/show.tsx`
13. `resources/js/pages/orders/confirmation.tsx`
14. `resources/js/pages/orders/index.tsx`
15. Checkout completion controllers, admin order page, dashboard, routes, and related feature tests

## Remaining Follow-Up

1. If guest-order write actions should become more broadly shareable later, review upload and state-change authorization before widening access beyond the guest session model.
