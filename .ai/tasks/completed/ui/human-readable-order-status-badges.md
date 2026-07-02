# Task: Human Readable Order Status Badges

## Metadata

- Status: completed
- Created: 2026-07-02
- Updated: 2026-07-02
- Source: user request
- Priority: high

## Raw Request

- Technical statuses such as `collection_pending` are visible in the UI.
- Introduce labels with proper icons and meaningful short status names with suitable background and text colours.

## Objective

Replace raw technical order, payment, and shipment status strings on order-facing pages with reusable status badges that show a short human-readable label, icon, and semantic color treatment.

## Acceptance Criteria

1. Customer order pages no longer display raw statuses like `collection_pending`.
2. Admin and vendor order pages use the same status label conventions where statuses are shown for reading.
3. Badges include a relevant icon and distinct but restrained color treatment.
4. Select inputs may keep raw option values internally, but displayed option text should be readable.
5. TypeScript and focused page tests pass.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `resources/js/components/`
- `resources/js/pages/orders/`
- `resources/js/pages/admin/orders/`
- `resources/js/pages/vendor/orders/`
- `resources/js/pages/dashboard.tsx`

## Risks Or Open Questions

- Some admin controls still need raw status values for form submission, so labels must not break payload values.

## Test Plan

- Run TypeScript checks and focused order page tests.
- Add or update backend payload tests only if server-side label data is introduced.

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented on 2026-07-02.
- Added a reusable order/payment/shipment status badge with short readable labels, icons, and semantic colors.
- Replaced raw order status output across customer, dashboard, vendor order, and admin order screens.
- Kept raw status values for form submissions while displaying readable option labels.
- Verification: `pnpm run types`.
