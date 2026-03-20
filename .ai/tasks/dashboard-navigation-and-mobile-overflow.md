# Task: Dashboard Navigation And Mobile Overflow

## Status

- Implementation completed on 2026-03-20
- Verified with focused Pest coverage, TypeScript checks, and production builds

## Goal

Improve dashboard navigation clarity and remove horizontal overflow issues across key authenticated pages on mobile.

## Final Product Decisions

1. The authenticated dashboard header now includes two top-left icon links:
   - main site using the existing site logo
   - dashboard using a dedicated dashboard icon treatment
2. Full-page horizontal scrolling was removed from the main authenticated dashboard, order, admin, and vendor surfaces.
3. Intentional component-level horizontal scrolling remains only where the content itself is legitimately wide.

## Implementation Summary

### Completed Frontend Work

1. Added a dedicated dashboard icon component for authenticated navigation.
2. Updated the shared authenticated sidebar header so the top-left area now shows:
   - main site logo link
   - dashboard logo link
   - breadcrumbs on medium screens and above
3. Fixed order progress layout to support the current five-step order flow without forcing a broken mobile width.
4. Reworked the main authenticated dashboard and order pages to avoid page-level horizontal overflow by:
   - replacing `overflow-x-auto` wrappers with `overflow-x-hidden`
   - adding `min-w-0` where flex and grid children could force width expansion
   - allowing action groups to wrap on smaller screens
5. Applied the same responsive cleanup pattern to the main admin and vendor authenticated pages, including:
   - order pages
   - vendor products and inquiries
   - admin approval and catalog maintenance pages
   - the YouTube connect page while preserving intentional `<pre>` scrolling

## Verification Notes

### Completed Checks

1. `vendor/bin/pint --dirty --format agent`
2. `npm run types`
3. `npm run build`
4. `php artisan test --compact tests/Feature/DashboardTest.php tests/Feature/OrderHistoryTest.php tests/Feature/OrderTrackingTest.php tests/Feature/OfflinePaymentOperationsTest.php tests/Feature/OrderOperationsTest.php`
5. `php artisan test --compact tests/Feature/DashboardTest.php tests/Feature/VendorProductIndexTest.php tests/Feature/Feature/VendorFeedbackTest.php tests/Feature/VendorInquiryFlowTest.php`

## Files Added Or Updated

1. `resources/js/components/dashboard-logo-icon.tsx`
2. `resources/js/components/app-sidebar-header.tsx`
3. `resources/js/components/order-progress.tsx`
4. `resources/js/pages/dashboard.tsx`
5. `resources/js/pages/orders/index.tsx`
6. `resources/js/pages/orders/show.tsx`
7. `resources/js/pages/admin/orders/index.tsx`
8. `resources/js/pages/admin/orders/show.tsx`
9. `resources/js/pages/vendor/orders/index.tsx`
10. `resources/js/pages/vendor/orders/show.tsx`
11. `resources/js/pages/vendor/products/index.tsx`
12. `resources/js/pages/vendor/feedback/create.tsx`
13. `resources/js/pages/vendor/inquiries/index.tsx`
14. `resources/js/pages/admin/product-categories/index.tsx`
15. `resources/js/pages/admin/product-colors/index.tsx`
16. `resources/js/pages/admin/youtube/connect.tsx`
17. `resources/js/pages/admin/vendor-inquiries/pending.tsx`
18. `resources/js/pages/admin/feedback/pending.tsx`
19. `resources/js/pages/admin/products/pending.tsx`
20. `resources/js/pages/admin/vendors/pending.tsx`

## Remaining Follow-Up

1. If you want stricter visual regression protection for mobile layouts later, add browser-level viewport coverage for
   the main authenticated pages.
