# Task: Cart And Checkout UX Refinements

## Metadata

- Status: planned
- Created: 2026-04-03
- Updated: 2026-04-03
- Source: user request + merged legacy note
- Priority: high

## Raw Request

- When seeing the cart, there should be a link to "Brows more products" or "Shop more" or something creative.
- Billing address should have a place to add the country. Now it's always the USA. By default, the country should be the current user's country.
- On the checkout page, if the user has selected billing details the same as shipping details, we can show a message something like "Your billing details are the same as your shipping details" and hide the billing details form.

## Objective

Improve the pre-purchase flow by making the cart easier to continue shopping from and making the checkout address experience more accurate, less redundant, and better aligned with the customer's actual country information.

## Acceptance Criteria

1. The cart page includes a clear call to continue shopping, with final copy chosen during implementation.
2. The billing address form includes a country field instead of assuming the USA.
3. The checkout defaults the billing country to the current authenticated user's country when that value is available.
4. A safe fallback country behavior exists for guests or users without a known country.
5. When billing is marked as the same as shipping, the billing address form is hidden.
6. When the billing form is hidden, a clear explanatory message is shown instead.
7. If billing is no longer marked as the same as shipping, the billing form becomes visible again.
8. Checkout validation and order-address persistence remain correct.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-guide.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/archive/checkout-hide-billing-form-when-same-as-shipping-merged-into-cart-and-checkout-ux-refinements.md`

## Likely Implementation Areas

- Cart page Inertia component
- Checkout page Inertia component and related form components
- Checkout controller payload and validation flow
- Order address persistence rules
- Focused cart and checkout feature tests

## Risks Or Open Questions

- The current source of truth for a user's country needs to be confirmed in the existing schema and profile flow.
- If no user-country field exists, the default-country behavior may need to rely on shipping data, geolocation, or a product decision.
- Backend validation may need to become conditional if billing fields are currently always required.

## Test Plan

- Run focused cart and checkout feature tests
- Add or update tests for billing-country handling and same-as-shipping visibility behavior

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Not started yet.
