# Task: Vendor Product Pricing Currency Label LKR

## Metadata

- Status: planned
- Created: 2026-04-06
- Updated: 2026-04-06
- Source: user request
- Priority: medium

## Raw Request

- The product create and edit page (`resources/js/pages/vendor/products/create.tsx`, `resources/js/pages/vendor/products/edit.tsx`) always uses LKR currency.
- Now it shows a dollar sign.

## Objective

Ensure the vendor product create and edit experiences display pricing consistently in the store's configured LKR currency so vendors do not see misleading dollar-based previews while entering or reviewing product prices.

## Acceptance Criteria

1. The pricing preview on the vendor product create page shows LKR-compatible labels instead of dollar-prefixed values.
2. The pricing preview on the vendor product edit page uses the same currency presentation as the create page.
3. The displayed currency format aligns with the application's configured base currency rather than a hardcoded USD-style symbol.
4. Focused tests or UI-facing assertions cover the expected pricing labels to prevent regression.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `resources/js/pages/vendor/products/create.tsx`
- `resources/js/pages/vendor/products/edit.tsx`
- `config/commerce.php`
- Related feature coverage for vendor product pages

## Risks Or Open Questions

- The current bug may be limited to preview text, but there may be other vendor-facing price labels still hardcoded to USD-style formatting.
- If a shared currency-formatting utility does not exist yet, the implementation should avoid duplicating formatting logic across multiple pages.

## Test Plan

- Add or update focused feature coverage for the vendor product create and edit pages
- Verify the rendered pricing preview uses the expected LKR label format on both pages

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
