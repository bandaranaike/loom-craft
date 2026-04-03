# Task: Product Reviews And Ratings

## Metadata

- Status: completed
- Created: 2026-04-03
- Updated: 2026-04-03
- Source: user request
- Priority: high

## Raw Request

- The user should rate the products after their product delivery. User can add a review. 1-5 stars.
- Reviews and ratings should be on the product page.

## Objective

Add a post-delivery review system so customers can leave 1 to 5 star ratings and written feedback for purchased products, and surface that social proof on the public product page.

## Acceptance Criteria

1. Customers can submit a review only after the relevant product has been delivered.
2. Reviews support a 1 to 5 star rating.
3. Reviews support written feedback content.
4. Product pages display review content and rating information.
5. Review visibility and submission rules prevent non-buyers or undelivered orders from posting reviews.
6. The review model and product-page presentation work correctly for products with no reviews.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-guide.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- Database schema for product reviews or ratings
- Order-to-product eligibility rules
- Product show controller payload
- Product page review UI
- Focused product and order feature tests

## Risks Or Open Questions

- The current schema may not yet contain a review table, so this task may require a new migration.
- A product decision may be needed on whether one delivered order item allows one review total or one review per delivered purchase.
- Moderation, editing, and vendor/admin response behavior are not yet defined.

## Test Plan

- Add or update focused feature tests for review eligibility and product-page display
- Run the minimum relevant Pest test files for product page and order-delivery review gating

## Documentation Updates Required

- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented `product_reviews` with one review per user per product and 1 to 5 star ratings plus written feedback.
- Review submission is restricted to authenticated customers with a delivered order containing the product.
- Product pages now show average rating, total review count, a themed review section, and the gated review form state.
- Focused verification passed with `php artisan test --compact tests/Feature/ProductShowTest.php tests/Feature/ProductReviewTest.php`, `pnpm run types`, and `vendor/bin/pint --dirty --format agent`.
