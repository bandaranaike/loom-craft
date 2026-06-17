# Task: Stock-Based Production Time Algorithm

## Metadata

- Status: completed
- Created: 2026-06-06
- Updated: 2026-06-08
- Source: user request
- Priority: high

## Raw Request

- Stock Algorithm
  - Normally, vendors do not have a huge number of pieces in available stock.
  - They made products on demand.
  - The production time depends on the quantity, number of colors, required pattern, and the product type.
    - Machine and Fabric prepare time: 2 days and do not depend on the quantity.
    - Weaving time depends on the quantity, the type of weaving, and number of colors.
      - Wall hangers: 2 days per item
      - Cushion covers: 6 hours per item
      - Bags: 1 day per item
  - When a product is added to the system, the vendor can specify the production time. It's always weaving time (`products.production_time_days`).
  - The product quantity (`products.pieces_count`) is always the number of available pieces, and it does not take any time to prepare.
  - When the user adds product items into the cart to buy, calculate the total time of the product preparation.
  - Create an algorithm that calculates the total preparation time by considering all possible factors.
  - Use knowledge of statistics, mathematics, critical thinking, and algorithms, and add missing factors to the algorithm.
  - It could be a mathematical formula or a program.
  - Laravel and PHP are more suitable for this algorithm. Use backend programming and show it in the front end using an API because it uses database values.
  - When the quantity of products is changed, the order preparation time should be recalculated.
  - The preparation time should be calculated if the available quantity is less than the quantity of products in the cart.
  - Cart item quantity can be updated in several frontend locations. Every quantity change must show the preparing time.
  - If the cart has multiple products, assume all product preparation runs in parallel. The order preparation time is always the preparation time of the most time-consuming product.
  - If the cart has more than 6 items, configurable through `.env`, append a warning such as: `The various product count is big in your cart and it may take longer than expected due to workload.`
  - If the calculated order processing time exceeds 60 days, configurable through `.env`, show `60+ days` instead of the actual large number and tell the customer to contact the vendor before placing the order.

## Objective

Build a backend production-time calculation service that estimates preparation lead time for cart and checkout items when requested quantity exceeds immediately available stock. The calculation must treat `products.pieces_count` as ready stock, use `products.production_time_days` as vendor-provided weaving time, include the fixed 2-day machine/fabric preparation time only when new production is required, and expose recalculated estimates to the frontend through cart/product APIs whenever quantities change.

## Acceptance Criteria

1. The system calculates production time only for the shortage quantity, where `shortage_quantity = max(0, requested_quantity - products.pieces_count)`.
2. Items fully covered by `products.pieces_count` show no additional production time.
3. Items with a shortage include the fixed 2-day machine/fabric preparation time.
4. Items with a shortage include weaving time based on `products.production_time_days`, shortage quantity, and any available product attributes such as product type, color count, and pattern complexity.
5. The calculation is centralized in a reusable Laravel service/action instead of duplicated in controllers or frontend code.
6. Cart add, cart quantity update, cart review, and checkout/order placement all use the same backend calculation.
7. Cart/product API responses expose enough fields for the frontend to show estimated preparation time, including available quantity, requested quantity, shortage quantity, setup days, weaving days, total days, and a customer-facing message.
8. When cart quantities change, the frontend refreshes or recalculates the preparation estimate from the backend response.
9. Multiple cart lines are aggregated carefully so the same product shortage is not undercounted when the product appears more than once.
10. Multi-product cart estimates clearly distinguish per-item preparation time from total order preparation time.
11. The order-level preparation estimate uses the longest parallel product preparation time because all product preparation is assumed to run in parallel.
12. Every frontend location that can create, increase, decrease, remove, or otherwise change cart item quantity shows the latest preparation estimate after the backend update.
13. If the configured large-cart item threshold is exceeded, append a workload warning to the preparation-time message.
14. The large-cart threshold defaults to `6` and is configurable through an environment-backed Laravel config value.
15. The checkout flow displays the final backend-confirmed preparation estimate before order placement.
16. Automated tests cover in-stock, partial-stock, out-of-stock, quantity-change, multi-item, large-cart warning, and checkout validation paths.
17. If a calculated product or cart preparation estimate exceeds the configured maximum display days, the customer-facing estimate shows `{maximum}+ days`, hides the actual calculated day count, and displays a vendor-contact warning before order placement.
18. The maximum preparation display threshold defaults to `60` and is configurable through `COMMERCE_PRODUCTION_TIME_MAX_DISPLAY_DAYS`.

## Proposed Algorithm

Use a deterministic backend formula for phase 1:

```text
available_quantity = max(0, product.pieces_count)
shortage_quantity = max(0, requested_quantity - available_quantity)

if shortage_quantity = 0:
    preparation_days = 0
else:
    setup_days = 2
    weaving_days = product.production_time_days * shortage_quantity
    complexity_multiplier = product_type_multiplier * color_multiplier * pattern_multiplier
    risk_buffer_days = ceil((setup_days + weaving_days) * buffer_rate)
    preparation_days = ceil(setup_days + (weaving_days * complexity_multiplier) + risk_buffer_days)
```

Suggested phase 1 defaults:

- `setup_days`: `2` when shortage quantity is greater than zero.
- `product.production_time_days`: vendor-entered weaving time per produced item.
- `product_type_multiplier`: `1.0` unless a product type-specific rule exists.
- `color_multiplier`: `1.0` initially, then increase when reliable color-count data exists.
- `pattern_multiplier`: `1.0` initially, then increase when reliable pattern-complexity data exists.
- `buffer_rate`: a configurable small buffer such as `0.10` to cover operational uncertainty.

For a cart:

```text
line_preparation_days = preparation days for each product line shortage
order_preparation_days = max(line_preparation_days)
large_cart_threshold = config('commerce.production_time_large_cart_threshold', 6)
max_display_days = config('commerce.production_time_max_display_days', 60)
show_large_cart_warning = cart_distinct_product_count > large_cart_threshold
show_vendor_contact_warning = order_preparation_days > max_display_days
display_order_preparation_days = min(order_preparation_days, max_display_days)
```

Use `max()` for the order estimate because product preparation is assumed to run in parallel. The total order preparation time is always the preparation time of the most time-consuming product in the cart.

If `show_large_cart_warning` is true, append a customer-facing warning to the estimate message:

```text
The various product count is big in your cart and it may take longer than expected due to workload.
```

The exact wording can be polished during implementation, but the meaning must stay clear: large carts may take longer than the calculated estimate because vendor/admin workload can increase.

If `show_vendor_contact_warning` is true, do not expose the actual calculated day count in the customer-facing total. Show `{max_display_days}+ days` and append a warning such as:

```text
The product order time is getting longer. Before placing this order, you must contact the vendor.
```

## Quantity Change Surfaces

The preparation estimate must be recalculated and shown anywhere the frontend can change cart quantity or cart contents. Known surfaces to audit include:

- Product detail page quantity selector before adding to cart.
- Product detail page add-to-cart action when the selected quantity is added.
- Cart page quantity stepper or quantity input.
- Cart page remove item action.
- Cart page restore item action if the UI supports undo/restore.
- Cart drawer, mini-cart, header cart, or side panel quantity controls if present.
- Checkout page order review quantity controls if quantity can still be edited there.
- Checkout page item removal if removal is allowed there.
- Any saved cart, wishlist-to-cart, buy-now, quick-add, product card, search-result, or related-product add-to-cart action that can set quantity.
- Any API endpoint or Inertia action that changes quantity, merges duplicate cart lines, clears cart items, or applies a cart state from the session.

Each surface should use the backend response rather than recalculating independently in React. If a surface does not currently show preparation time, add an inline estimate or update the shared cart summary/preparation-time component after the quantity change succeeds.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/completed/product/product-quantity.md`
- `.ai/tasks/completed/commerce/cart-and-checkout-ux-refinements.md`

## Likely Implementation Areas

- Product model fields: `products.pieces_count`, `products.production_time_days`, product type/category/color/pattern data if available
- Commerce config for a large-cart threshold, defaulting to `6` from an environment variable
- Cart add/update controllers or actions
- Checkout/order placement validation
- Inertia product detail page quantity controls
- Inertia cart and checkout pages
- Any mini-cart, cart drawer, quick-add, buy-now, product-card, wishlist-to-cart, search-result, or related-product quantity/add-to-cart controls if present
- API/resource payloads that return cart line availability and preparation estimates
- A reusable Laravel service/action such as `ProductionTimeEstimator`
- Pest feature/unit tests for product/cart/checkout estimate behavior

## Risks Or Open Questions

- Is `products.production_time_days` always per item, or can vendors enter total weaving time for a batch?
- Should setup time always be exactly 2 calendar days, or should it use business days and exclude holidays?
- Should the final estimate be displayed in days only, or support hours for cushion covers where the source rule is 6 hours per item?
- Where should number of colors and pattern complexity come from in the current schema?
- Parallel preparation is confirmed for phase 1: order preparation time is the most time-consuming product preparation time in the cart.
- Should the estimate include vendor workload from other open orders, or only the current cart?
- Should the configurable buffer rate be global, per category, per vendor, or disabled for phase 1?
- Should the estimate be stored on the order at placement time for auditability, or recalculated from product data only?

## Test Plan

- Unit test the estimator for fully in-stock products.
- Unit test partial-stock products where only the shortage quantity contributes to production time.
- Unit test out-of-stock products where the full requested quantity contributes to production time.
- Unit test setup time is included only when shortage quantity is greater than zero.
- Unit test cart-level aggregation for multiple products.
- Unit test cart-level order preparation time uses the maximum product preparation time, not the sum.
- Unit test large-cart warning appears when distinct cart product count exceeds the configured threshold.
- Unit test duplicate product lines aggregate requested quantity before calculating shortage.
- Unit test long estimates are capped at the configured maximum display days and require vendor contact.
- Feature test cart add/update responses expose preparation estimate payloads.
- Feature test every discovered quantity-change endpoint returns or refreshes preparation estimate payloads.
- Feature test checkout/order placement uses the final backend estimate.
- Feature test cart and checkout payloads hide actual long preparation day totals and show `{maximum}+ days`.
- Frontend test or focused smoke check that quantity changes refresh the displayed estimate.

## Documentation Updates Required

- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented `ProductPreparationEstimator` with shortage-only production calculation, configurable setup days, buffer rate, fallback weaving days, and large-cart threshold.
- Cart summaries now expose per-line shortage/preparation fields and an order-level `preparation_estimate`.
- Cart and checkout render preparation estimates from the backend payload; product detail quantity selection uses the same formula with backend-provided config for instant inline feedback before add-to-cart.
- Order preparation time uses the longest product preparation time because product preparation is assumed to run in parallel.
- Large carts append the configured workload warning when distinct cart product count exceeds the threshold.
- Long preparation estimates are capped by `COMMERCE_PRODUCTION_TIME_MAX_DISPLAY_DAYS` (default `60`) in product, cart, and checkout displays. When the calculated time exceeds that threshold, the UI shows `60+ days` and tells the customer to contact the vendor before placing the order instead of showing the actual calculated day count.
- Added focused Pest coverage for estimator behavior, cart payloads, large-cart warning, and checkout preparation payloads.
