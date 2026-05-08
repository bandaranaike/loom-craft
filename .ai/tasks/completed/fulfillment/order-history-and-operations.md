# Task: Order History And Operations

## Status

- Implementation completed on 2026-03-20
- Verified with focused Pest coverage after enabling the SQLite PDO driver in this environment

## Goal

Give customers, vendors, and admins the correct order visibility and order-management controls based on their role,
while keeping mixed-vendor orders understandable and operationally manageable.

## Final Product Decisions

1. Admin order deletion is implemented as soft delete on `orders`.
2. Vendors can view mixed-vendor orders when at least one line item belongs to them, but only their own items are
   visually emphasized.
3. Admin and vendors can both mark eligible orders as `shipped`.
4. Offline payment review for `bank_transfer` and `cod` is available to both admin and eligible vendors.

## Implementation Summary

### Completed Backend Work

1. Added soft deletes to `orders` using `deleted_at` and enabled the `SoftDeletes` trait on `Order`.
2. Updated order authorization rules so:
   - customers can only view their own orders
   - admins can list all orders, update order status, and soft delete orders
   - vendors can only view and operate on orders containing their products
3. Added admin order-status update and soft-delete endpoints.
4. Added vendor order show, shipped-status update, and offline payment-review endpoints.
5. Reworked vendor order queries from line-item-only listing to full order listing plus vendor-scoped detail loading.
6. Extended DTOs and actions so vendor order pages can identify which line items belong to the authenticated vendor.
7. Updated the progress lifecycle to include a `shipped` step.

### Completed Frontend Work

1. Customer order history remains available only for the authenticated order owner.
2. Admin order pages now expose:
   - separate order-status controls
   - offline payment review controls
   - soft delete action
3. Vendor order pages now include:
   - a full order index
   - a vendor-specific order detail page
   - shipped-status action
   - offline payment review controls
4. Mixed-vendor orders now visually highlight the vendor's own products while keeping other vendors' products visible in
   a muted treatment.

## Verification Notes

### Completed Checks

1. `php artisan wayfinder:generate --with-form --no-interaction`
2. `vendor/bin/pint --dirty --format agent`
3. `npm run types`
4. `php artisan test --compact tests/Feature/OrderHistoryTest.php tests/Feature/OrderTrackingTest.php tests/Feature/OfflinePaymentOperationsTest.php tests/Feature/OrderOperationsTest.php`

### Environment Notes

1. Installed the SQLite PDO driver for the active PHP CLI so Pest can run against `sqlite :memory:`.

## Files Added Or Updated

1. `database/migrations/2026_03_20_034737_add_deleted_at_to_orders_table.php`
2. `app/Models/Order.php`
3. `app/Policies/OrderPolicy.php`
4. `app/Actions/Order/ListAdminOrders.php`
5. `app/Actions/Order/ShowAdminOrder.php`
6. `app/Actions/Order/ShowOrder.php`
7. `app/Actions/Order/ListVendorOrders.php`
8. `app/Actions/Order/ShowVendorOrder.php`
9. `app/Actions/Order/BuildOrderProgress.php`
10. `app/DTOs/Order/AdminOrderListItem.php`
11. `app/DTOs/Order/AdminOrderSummaryResult.php`
12. `app/DTOs/Order/OrderItemSummary.php`
13. `app/DTOs/Order/VendorOrderListItem.php`
14. `app/DTOs/Order/VendorOrderListResult.php`
15. `app/DTOs/Order/VendorOrderSummaryResult.php`
16. `app/Http/Controllers/Admin/OrderController.php`
17. `app/Http/Controllers/Vendor/OrderController.php`
18. `app/Http/Requests/Admin/UpdateOrderStatusRequest.php`
19. `app/Http/Requests/Admin/UpdateOfflineOrderRequest.php`
20. `app/Http/Requests/Vendor/UpdateOrderStatusRequest.php`
21. `app/Http/Requests/Vendor/UpdateOfflineOrderRequest.php`
22. `resources/js/pages/admin/orders/index.tsx`
23. `resources/js/pages/admin/orders/show.tsx`
24. `resources/js/pages/vendor/orders/index.tsx`
25. `resources/js/pages/vendor/orders/show.tsx`
26. `routes/web.php`
27. `tests/Feature/OrderOperationsTest.php`
28. `tests/Feature/OrderTrackingTest.php`
29. `tests/Feature/OfflinePaymentOperationsTest.php`

## Remaining Follow-Up

1. If you want deleted orders visible in a recycle-bin style admin screen later, add a `withTrashed()` admin listing and a
   restore flow.
2. If the business later needs stricter state-transition rules, centralize them in a dedicated order workflow layer.
