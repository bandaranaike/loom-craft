# Task: Admin Orders And Status Management

## Status

- Implementation completed on 2026-03-21
- Verified with focused Pest coverage on 2026-03-21

## Goal

Complete the admin order-management flow so admins can review all orders, filter them by lifecycle status, and inspect
offline-payment proof details without affecting the existing vendor order experience.

## Final Product Decisions

1. The existing dedicated admin orders page was reused instead of merging admin behavior into the vendor page.
2. Admin order filtering uses the existing order-status vocabulary: `pending`, `paid`, `confirmed`, `shipped`,
   `delivered`, and `cancelled`.
3. Offline payment proof rendering on the admin detail page is now method-aware and works for both `bank_transfer` and
   `cod` orders when proof metadata exists.

## Implementation Summary

### Completed Backend Work

1. Extended admin order index request data to accept a sanitized optional `status` query parameter.
2. Updated the admin order listing action to return all orders by default and narrow the result set when a valid status
   filter is present.
3. Added admin order filter metadata to the Inertia payload so the frontend receives:
   - the active selected status
   - the available status options
4. Reused the existing admin authorization and order-management endpoints without altering vendor-scoped behavior.

### Completed Frontend Work

1. Updated the existing admin orders index page to include a status filter control backed by the query string.
2. Added visible filter state feedback on the admin orders index so admins can see how many matching orders are shown.
3. Kept the current admin detail page and generalized the payment-proof panel so it supports any offline order with an
   uploaded proof payload.
4. Preserved the current vendor order pages and interactions unchanged.

## Verification Notes

### Completed Checks

1. `vendor/bin/pint --dirty --format agent`
2. `npm run types`
3. `php artisan test --compact tests/Feature/OrderOperationsTest.php tests/Feature/OfflinePaymentOperationsTest.php`

## Files Added Or Updated

1. `app/Actions/Order/ListAdminOrders.php`
2. `app/DTOs/Order/OrderIndexData.php`
3. `app/DTOs/Order/AdminOrderListResult.php`
4. `resources/js/pages/admin/orders/index.tsx`
5. `resources/js/pages/admin/orders/show.tsx`
6. `tests/Feature/OrderOperationsTest.php`
7. `tests/Feature/OfflinePaymentOperationsTest.php`

## Remaining Follow-Up

1. If customer-facing or vendor-facing order lists later need the same status-filter behavior, the sanitized
   `OrderIndexData` status field can be reused there.
