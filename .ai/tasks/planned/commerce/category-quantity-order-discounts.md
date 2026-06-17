# Task: Category Quantity Order Discounts

## Metadata

- Status: planned
- Created: 2026-06-15
- Updated: 2026-06-15
- Source: user request
- Priority: medium

## Raw Request

- The order can have a discount with the order quantity.
    - We should develop an algorithom for this.
    - Every category has discount quantities
        - Bags if qty >=10 10% discounts. if qty >= 20, 15%
        - Wall Hangers if qty >= 6 10% discounts. if qty >= 12, 20%
        - Cushion covers if qty >= 10 10% discounts. if qty >= 20, 15%
    - Those values should be configurable. Provided above values are defaults.

## Objective

Add configurable quantity-based discount tiers that apply during cart, checkout, and order creation based on product category quantities. The implementation should calculate the best matching tier per category, use the configured defaults for Bags, Wall Hangers, and Cushion covers, and persist the charged pricing on order items so historical orders are not affected by later configuration changes.

## Acceptance Criteria

1. Admin-configurable quantity discount tiers exist per product category.
2. Default tiers are available for:
   - Bags: quantity `>= 10` gives `10%`, quantity `>= 20` gives `15%`.
   - Wall Hangers: quantity `>= 6` gives `10%`, quantity `>= 12` gives `20%`.
   - Cushion covers: quantity `>= 10` gives `10%`, quantity `>= 20` gives `15%`.
3. Cart and checkout totals apply the highest eligible quantity tier for each category.
4. The algorithm handles multiple cart lines in the same category by using the combined category quantity.
5. Order placement persists the charged unit price, line discount amount, line total, and any discount context needed for reporting.
6. Category quantity discounts interact predictably with existing product/category sale discounts, with one centralized pricing path defining the order of operations.
7. Customer-facing cart and checkout pages show the applied quantity discount clearly when a tier is active.
8. If no tier is eligible, pricing remains unchanged.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/tasks/completed/product/products-discounts.md`

## Likely Implementation Areas

- Product category model, migration, factory, and admin category management.
- Cart and checkout pricing services or actions.
- Order creation actions/controllers and `order_items` persistence.
- Inertia cart and checkout pages that display totals and discount lines.
- Feature tests covering cart, checkout, and order creation behavior.

## Risks Or Open Questions

- Confirm whether category quantity tiers should be managed in the existing category admin screen or a dedicated discount settings screen.
- Confirm whether quantity tiers apply before or after existing product/category sale discounts.
- Confirm how to handle products assigned to multiple categories if that is supported by the current schema.
- Confirm whether the quantity threshold is based on all products in a category across the full order or only identical product quantities.

## Test Plan

- Add or update feature tests for each default category tier threshold.
- Test that the highest eligible tier wins when multiple thresholds are met.
- Test that multiple cart lines in the same category combine quantities for tier eligibility.
- Test that ineligible quantities do not change totals.
- Test order creation persists the final charged prices and discount context.
- Run the focused Pest tests with `php artisan test --compact` for the affected files or filters.

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

