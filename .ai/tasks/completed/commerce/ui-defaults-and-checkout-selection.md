# Task: UI Defaults And Checkout Selection

## Status

- Implementation completed on 2026-03-20
- Verified with focused Pest coverage, TypeScript checks, and a production build

## Goal

Make the storefront and dashboard experience default to the intended presentation mode, and ensure the checkout form defaults to the preferred payment method.

## Final Product Decisions

1. The global appearance default is now `light` for first-load visits with no saved preference.
2. Explicit appearance choices remain persistent through the existing cookie and local-storage flow.
3. Checkout defaults to `Stripe (Card)` when Stripe is configured.
4. If Stripe is unavailable, checkout falls back to the first available payment method instead of defaulting to a broken choice.

## Implementation Summary

### Completed Backend Work

1. Updated the shared appearance middleware so the Blade shell defaults to `light` instead of `system`.
2. Updated the checkout view payload to expose a dedicated `default_payment_method` prop.
3. Reordered checkout payment methods so Stripe is presented first in the available options.
4. Chose the checkout default payment method server-side, preferring Stripe only when it is actually configured.

### Completed Frontend Work

1. Updated the appearance bootstrap hook so first-load visits persist `light` as the initial preference.
2. Kept the existing explicit appearance toggles intact, including `dark` and `system`.
3. Updated checkout form initialization to use the server-provided default payment method instead of relying only on array order.
4. Normalized the checkout label copy to `Stripe (Card)`.

## Verification Notes

### Completed Checks

1. `php artisan test --compact tests/Feature/CheckoutTest.php tests/Feature/Settings/AppearanceDefaultsTest.php`
2. `vendor/bin/pint --dirty --format agent`
3. `npm run types`
4. `npm run build`

## Files Added Or Updated

1. `app/Actions/Order/ShowCheckout.php`
2. `app/Http/Controllers/CheckoutController.php`
3. `app/Http/Middleware/HandleAppearance.php`
4. `resources/js/hooks/use-appearance.tsx`
5. `resources/js/pages/checkout.tsx`
6. `resources/views/app.blade.php`
7. `tests/Feature/CheckoutTest.php`
8. `tests/Feature/Settings/AppearanceDefaultsTest.php`

## Remaining Follow-Up

1. If appearance preferences later need to sync to user accounts across devices, move the source of truth from browser storage into a persisted server-side profile setting.
