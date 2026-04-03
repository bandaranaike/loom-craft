# Product Quantity

## Goal
Use `products.pieces_count` as the current immediately available quantity, while still allowing customers to request more than that amount with a clear production delay warning.

## Current Context
- `products.pieces_count` already exists and represents the number of currently available pieces.
- `products.production_time_days` already exists and should be used in the customer-facing delay message.
- Cart quantity can be changed both when adding a product and later from the cart/checkout flow.

## Required Behavior
1. Treat `products.pieces_count` as available stock for immediate fulfillment.
2. Allow customers to add a quantity greater than `pieces_count`.
3. When requested quantity is greater than `pieces_count`, show this warning to the user with clearer wording:
   - `This quantity is not currently in stock. Your order will require additional production time and is expected to take about {production_time_days} days.`
4. The warning is not a popup or modal.
5. The warning is shown inline on the page where the quantity issue is detected.
6. The warning can be dismissed by the user.
7. After dismissal, the same warning should not appear again on the same page during the same session unless the page context changes in a meaningful way.
8. Re-evaluate this rule:
   - when adding an item to cart
   - when increasing quantity in cart
   - when reviewing cart before checkout
   - when placing the order
9. If `production_time_days` is missing while quantity exceeds `pieces_count`, define and implement a fallback message or validation rule before release.

## Backend Scope
- Centralize the quantity availability check in one reusable service/action/helper instead of duplicating the comparison.
- Ensure cart item updates and checkout/order placement both use the same rule.
- Return enough state to the frontend to distinguish:
  - quantity fully in stock
  - quantity exceeds available pieces
  - warning message to display

## Frontend Scope
- Show the warning on the product detail page when the chosen quantity exceeds `pieces_count`.
- Show the same warning in the cart when a cart line exceeds `pieces_count`.
- Render the warning inline within the relevant page layout, not as a popup.
- Make the warning dismissible.
- Persist dismissal for the same page in the same session so the warning does not immediately reappear after being closed.
- Prevent silent checkout with outdated assumptions; the cart/checkout UI should reflect the final backend validation result.

## Suggested Data Contract
- For cart and product quantity-related responses, expose fields such as:
  - `pieces_count`
  - `production_time_days`
  - `requested_quantity`
  - `exceeds_available_stock`
  - `stock_delay_message`

## Edge Cases
- `pieces_count` is `null`
- `pieces_count` is `0`
- `production_time_days` is `null`
- cart quantity is reduced back within available stock
- multiple quantity updates occur before checkout

## Tests
- Product page quantity behavior when selected quantity is within stock
- Product page quantity behavior when selected quantity exceeds stock
- Product page warning dismissal behavior within the same session
- Cart quantity update warning behavior
- Cart warning dismissal behavior within the same session
- Checkout validation when cart quantity exceeds stock
- Order placement behavior when quantity exceeds stock

## Acceptance Criteria
- Customers can request more than available stock.
- Delay warning appears consistently anywhere quantity is edited or validated.
- Delay warning uses polished customer-facing English.
- Delay warning is inline and dismissible.
- Once dismissed, the warning stays hidden for the same page in the same session.
- Checkout does not ignore quantity-vs-stock validation.
- Quantity logic is implemented once and reused across product, cart, and checkout flows.
- Automated tests cover the main stock and over-stock paths.
