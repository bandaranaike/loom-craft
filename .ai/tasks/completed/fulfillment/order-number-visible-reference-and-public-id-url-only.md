# Task: Order Number Visible Reference And Public Id Url Only

## Metadata

- Status: completed
- Created: 2026-07-02
- Updated: 2026-07-02
- Source: user request
- Priority: high

## Raw Request

- The user vissible order number should be `odrders.order_number` column.
- The public id should use only for urls.

## Objective

Make `orders.order_number` the human-visible order reference everywhere customers read or copy an order number, while keeping `orders.public_id` as the opaque lookup key used only in URLs and route binding. This should remove any remaining public-facing dependence on the public id for display text.

## Acceptance Criteria

1. Customer-facing order screens display `orders.order_number` instead of `orders.public_id` or a fallback `Order #id` reference.
2. Public order URLs continue to use `orders.public_id` or route binding, and the URL behavior does not change.
3. Order confirmations, account views, and other user-facing order references all use the order number consistently.
4. Internal or admin-only views may retain public id exposure where it is operationally useful, but public presentation does not.
5. Tests cover the main customer-facing order views and confirm the visible reference is the order number.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `resources/js/pages/orders/`
- `resources/js/pages/vendor/orders/`
- `resources/js/pages/admin/orders/`
- `resources/js/hooks/use-public-order-reference.ts`
- `resources/js/components/`
- `app/Actions/Order/`
- `app/Http/Resources/Api/V1/`
- `tests/Feature/`

## Risks Or Open Questions

- None remaining for the implemented web views.

## Test Plan

- Add or update focused tests for order detail, confirmation, and listing pages that check the displayed reference.
- Run the smallest relevant Pest suite with `php artisan test --compact`.

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented on 2026-07-02.
- Added `order_number` to order DTO payloads for customer, admin, dashboard, and vendor order views.
- Updated React order pages and shared reference components to display/copy `order_number`.
- Kept `public_id` usage for customer order URLs and bank-transfer upload routes.
- Added focused assertions for order-number payloads.
