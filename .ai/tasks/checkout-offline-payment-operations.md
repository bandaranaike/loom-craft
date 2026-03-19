# Task: Checkout Offline Payment Operations

## Status

- Implementation completed on 2026-03-19
- Pending final runtime verification after enabling the SQLite PDO driver for Pest in this environment

## Goal

Simplify checkout so shipping responsibility is always platform-handled, and complete the offline payment flow for
`bank_transfer` and `cod` with the operational tools the admin team needs after checkout.

## Final Product Decisions

1. Bank transfer proof uses one final slip only. Latest upload replaces the previous file.
2. Admin payment review options are `Payment success` and `Payment failed`.
3. COD payment status changes are allowed at any admin-reviewed step. There is no delivery-only restriction.

## Implementation Summary

### Completed Backend Work

1. Checkout now validates and stores `shipping_responsibility` as `platform` only.
2. Payment slip metadata is stored directly on `payments`.
3. Bank transfer slip upload now accepts PDF and image files, replaces the previous slip, and stores:
   - storage path
   - original filename
   - mime type
   - uploaded timestamp
4. Admin-only offline review routes now support:
   - payment status updates for `bank_transfer` and `cod`
   - order status updates for `bank_transfer` and `cod`
   - verification metadata via `verified_by` and `verified_at`
5. Order DTOs and actions now expose payment-proof data to customer and admin pages.

### Completed Frontend Work

1. The checkout page no longer exposes the shipping responsibility selector.
2. Checkout now presents shipping as platform-handled only.
3. Customer order pages now include a bank transfer slip upload surface with clear order context.
4. The admin order page now includes:
   - offline payment review controls
   - offline order status controls
   - uploaded bank transfer proof preview/download

## Verification Notes

### Completed Checks

1. `php artisan wayfinder:generate --with-form --no-interaction`
2. `vendor/bin/pint --dirty --format agent`
3. `npm run types`
4. `npm run build`
5. `php -l` on the changed PHP files

### Environment Blocker

1. `php artisan test --compact tests/Feature/OfflinePaymentOperationsTest.php` could not run successfully because the current environment is missing the SQLite PDO driver required by the test suite's `sqlite :memory:` connection.

## Files Added Or Updated

1. `app/Http/Controllers/OrderBankTransferSlipController.php`
2. `app/Http/Controllers/Admin/OrderController.php`
3. `app/Http/Requests/Order/StoreBankTransferSlipRequest.php`
4. `app/Http/Requests/Admin/UpdateOfflineOrderRequest.php`
5. `app/Actions/Order/ShowAdminOrder.php`
6. `app/DTOs/Order/AdminOrderSummaryResult.php`
7. `database/migrations/2026_03_19_135305_add_bank_transfer_proof_to_payments_table.php`
8. `resources/js/pages/admin/orders/show.tsx`
9. `tests/Feature/OfflinePaymentOperationsTest.php`
10. Checkout, order, payment, policy, route, and existing test files needed for the flow

## Remaining Follow-Up

1. Re-run the new Pest coverage after `pdo_sqlite` is available in the environment.
2. If needed later, add stricter business transition rules for offline order statuses. Current admin review intentionally allows the approved broad update path you requested.
