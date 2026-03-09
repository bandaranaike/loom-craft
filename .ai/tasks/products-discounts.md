# Task: Products Discounts

## Goal
Add a discount system for products and product categories so customers always see the best available discounted price across product cards, product details, cart, and checkout.

## Why
- Improves merchandising flexibility for campaigns and category-wide promotions.
- Makes discounted pricing consistent across the full buying journey.
- Prevents pricing conflicts by enforcing a single effective discount per product.

## Scope
1. Vendors or admins can assign a direct discount to an individual product.
2. Admins can assign a discount to a product category.
3. If both product-level and category-level discounts exist, the product uses the higher discount.
4. Discounted pricing is shown on:
- Product cards
- Product details page
- Cart page
- Checkout page
5. The original price remains visible as a struck-through comparison price.
6. Discounted products show a visible ribbon or badge on cards and the details page.

## Discount Rules
1. Product discount and category discount are percentage-based unless the domain requires fixed-amount discounts later.
2. Effective discount for a product is:
- Product discount, if only product discount exists
- Highest category discount, if only category discount exists or the product belongs to multiple discounted categories
- Max of product discount and highest category discount, if both exist
3. Effective price must never be below zero.
4. All price calculations must happen on the backend; the frontend only renders returned values.
5. Cart and checkout must use the effective discounted price at render time and order placement time.

## Data Model Requirements
1. Product-level discount
- Add nullable discount fields to products or use a dedicated discounts table if the codebase already leans that way.
- Minimum required data:
  - `discount_percentage`
  - Optional scheduling fields for future-proofing:
  - `discount_starts_at`
  - `discount_ends_at`

2. Category-level discount
- Add discount fields to `product_categories` or create a dedicated category discount model if needed.
- Minimum required data:
  - `discount_percentage`
  - Optional scheduling fields for future-proofing:
  - `discount_starts_at`
  - `discount_ends_at`

## Backend Work
1. Pricing calculation
- Introduce a single reusable pricing/effective-discount calculation path.
- Avoid duplicating discount logic across controllers, DTOs, and actions.
- Ensure product listing, product details, cart, and checkout all consume the same computed values.

2. Product payloads
- Extend product DTOs/resources/view models with:
  - `original_price`
  - `discounted_price`
  - `effective_discount_percentage`
  - `has_discount`

3. Validation
- Discount percentage must be numeric and constrained to a safe range such as `0` to `100`.
- If scheduling is implemented, `ends_at` must be after `starts_at`.

4. Order integrity
- Order placement must persist the actual charged unit price and any discount context needed for later reporting.
- Do not recalculate historical order pricing from live product/category values after the order is placed.

## Frontend Work
1. Product card UI
- Add a top-right discount ribbon/badge.
- Show discounted price as the primary price.
- Show original price as a smaller, muted, struck-through value.

2. Product details UI
- Add the same discount ribbon/badge treatment.
- Show discounted price prominently near the main buy section.
- Show original struck-through price with a distinct but subdued color treatment.

3. Cart and checkout UI
- Show discounted unit price and totals.
- If useful, include a small discount indicator so the customer understands why the price changed.

## UX Rules
1. The discounted price must always be more visually prominent than the original price.
2. The original price must remain readable but clearly secondary.
3. Ribbon/badge styling must work on both desktop and mobile.
4. Avoid inconsistent labels such as mixing `Sale`, `% Off`, and `Discount` without a clear rule.

## Suggested Admin/Vendor Controls
1. Product management
- Add direct product discount input where product pricing is managed.

2. Category management
- Add category discount controls in category administration.

3. Visibility
- If no discount is active, no ribbon and no struck-through price should appear.

## Testing Requirements
1. Feature tests
- Product with only a product discount uses product discount.
- Product with only a category discount uses category discount.
- Product with both product and category discounts uses the higher discount.
- Product in multiple discounted categories uses the highest category discount.
- Product card/detail payloads include correct computed prices.
- Cart uses the discounted price.
- Checkout uses the discounted price.
- Placed orders persist the charged discounted price correctly.

2. Edge-case tests
- Zero discount behaves as no discount.
- Hundred percent discount produces zero price and does not go negative.
- Expired or inactive discounts are ignored if scheduling is implemented.

## Acceptance Criteria
1. A product can have its own discount.
2. A category can have its own discount.
3. The effective discount is always the highest applicable discount.
4. Product cards and product details show:
- Discount ribbon/badge
- Discounted price
- Original struck-through price
5. Cart and checkout use the same effective discounted price shown earlier in the journey.
6. Discount calculation logic is centralized and covered by tests.

## Open Decisions
1. Who can manage product discounts: vendor only, admin only, or both?
2. Who can manage category discounts: admin only is likely safest unless the app has vendor-scoped categories.
3. Should discount scheduling be built now or left as nullable future-ready fields?
4. Should the order keep explicit discount fields such as `discount_percentage` and `discount_amount` for reporting?
