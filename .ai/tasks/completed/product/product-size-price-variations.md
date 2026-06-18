# Task: Product Size Price Variations

## Metadata

- Status: completed
- Created: 2026-06-18
- Updated: 2026-06-18
- Source: user request
- Priority: high

## Raw Request

In the products, there are different sizes and they have different prices. There should be a place to add those sizes and prices when creating or updating products. Example 16*16 = 2600, 18*18 = 2800, and 20*20=3200, but same fabric same design. Variation can be any number. Please create a task first.

Follow-up clarification: When changed the size, the price should be changed as well.

## Objective

Add product size-price variations so a single product can represent the same fabric and design while offering any number of purchasable sizes, each with its own price. Vendor product create and update flows must allow managing these variations, and customer-facing product, cart, checkout, and order flows must preserve the selected size and charged price.

## Acceptance Criteria

1. Product create and edit forms include a repeatable size-price variation section.
2. Vendors can add, update, remove, and reorder any number of variations for one product.
3. Each variation stores a human-readable size label, such as `16*16`, `18*18`, or `20*20`, and a required price.
4. Variation prices are validated as positive monetary values.
5. A product can still support existing single-price behavior where needed, or the migration path clearly maps the existing product price to a default variation.
6. Customer-facing product pages let customers choose an available size before adding the product to cart.
7. Customer-facing product pages update the displayed price immediately when the selected size changes.
8. Cart and checkout lines display the selected size and use the selected variation price.
9. Order items persist the selected size label, variation identifier if applicable, and charged unit price so historical orders are not affected by later product edits.
10. Backend validation prevents adding an invalid, deleted, or mismatched product variation to cart or checkout.
11. Existing product discounts, commission calculations, and public product URLs continue to work with variation-based pricing.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/tasks/completed/product/products-discounts.md`
- `.ai/tasks/completed/product/product-quantity.md`

## Likely Implementation Areas

- Product model, migration, factory, and data casting or relationships for variations.
- Vendor product create/edit Form Requests and controllers.
- Vendor product create/edit Inertia React pages.
- Public product detail page and add-to-cart flow.
- Cart, checkout, and order creation pricing paths.
- Order item persistence for selected variation details.
- Feature tests for product management, cart pricing, checkout, and order placement.

## Risks Or Open Questions

- Decide whether variations should be stored in a dedicated `product_variations` table or a JSON column based on current product and order schemas.
- Confirm whether every product must have at least one variation or whether variations are optional for products with one fixed price.
- Confirm whether stock quantity should remain product-level or become variation-level in a later task.
- Confirm how sale discounts apply when each variation has a different base price.
- Confirm whether size labels need standard formatting rules beyond required non-empty text.

## Test Plan

- Add or update feature tests for creating a product with multiple size-price variations.
- Test updating product variations, including adding, removing, and changing prices.
- Test validation for missing labels, invalid prices, and duplicate or empty variations if duplicates are disallowed.
- Test product detail add-to-cart behavior with a selected variation.
- Test product detail payload includes variation prices so the UI can update price on size change.
- Test cart and checkout totals use the selected variation price.
- Test order placement persists selected size and charged unit price.
- Run focused Pest tests with `php artisan test --compact` for the affected files or filters.

## Documentation Updates Required

- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented `product_variations` with size labels, vendor prices, calculated selling prices, dimensions, and sort order.
- Vendor create/edit product forms now manage repeatable size-price rows with length, width, and height on each variation.
- Public product pages now update the visible customer price and dimensions when the selected size changes.
- Cart lines are separated by selected variation and display the selected size.
- Checkout and order placement preserve the selected variation label and charged unit price on `order_items`.
- Product length, width, and height were moved from `products` to `product_variations`; `products.dimension_unit` remains shared across a product's variations.
- Added focused Pest coverage in `tests/Feature/ProductVariationTest.php`.
