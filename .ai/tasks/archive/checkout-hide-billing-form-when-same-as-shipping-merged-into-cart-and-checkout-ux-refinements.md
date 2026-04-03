# Task: Checkout Hide Billing Form When Same As Shipping

## Metadata

- Status: planned
- Created: 2026-04-03
- Updated: 2026-04-03
- Source: migrated legacy note
- Priority: medium

## Raw Request

On the checkout page, if the user has selected billing details the same as shipping details, we can show a message something like "Your billing details are the same as your shipping details" and hide the billing details form.

## Objective

Improve checkout clarity by hiding unnecessary billing-address inputs when the customer chooses to reuse the shipping address, while still making the resulting behavior obvious and trustworthy.

## Acceptance Criteria

1. When the customer selects billing details same as shipping details, the billing form is hidden.
2. A clear message explains that the billing details are the same as the shipping details.
3. If the customer changes the selection away from shared billing details, the billing form becomes visible again.
4. Existing checkout submission and validation behavior remains correct.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-guide.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- Checkout controller payload and validation flow
- `resources/js/pages/checkout*`
- Related checkout form components
- Focused checkout feature tests

## Risks Or Open Questions

- The exact current field names and billing-address toggle behavior need to be confirmed in code before implementation.
- If validation currently always expects billing fields, backend rules may also need to become conditional.

## Test Plan

- Run focused checkout feature tests
- Add or update tests for the same-as-shipping toggle behavior

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`
- Any checkout-flow note in `.ai/knowledge/core/order-process.md` if behavior changes materially

## Completion Notes

Not started yet.
