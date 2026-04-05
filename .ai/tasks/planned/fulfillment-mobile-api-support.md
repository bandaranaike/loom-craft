# Task: Fulfillment Mobile API Support

## Metadata

- Status: planned
- Created: 2026-04-03
- Updated: 2026-04-03
- Source: user request
- Priority: high

## Raw Request

- A mobile app should use the API of the LoomCraft website.
- All processing orders should be visible in the app.
- Users should be able to open an order and:
    - print stickers
    - change order status to shipped or delivered
- Additional useful fulfillment features may be added if necessary.

## Objective

Define and later implement the backend API surface required to support a fulfillment-focused mobile app. The API should expose processing orders, detailed order payloads for
packing and shipping, printable sticker data, and safe status-update actions that match the real delivery workflow.

## Acceptance Criteria

1. Required mobile-app API endpoints are identified for order listing, order detail, sticker payload retrieval, and status updates.
2. Authentication and authorization requirements for the mobile app are defined.
3. The API contract includes the exact order data needed for packing and dispatch work.
4. The API design accounts for current and proposed delivery statuses.
5. The API design includes failure states and validation rules for invalid status transitions.
6. The implementation plan identifies whether the current Laravel app should expose JSON API routes, Inertia-adjacent controllers, or a separate API namespace.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `routes/api.php` or a new API route surface if approved
- Order and shipment controllers/services
- Auth strategy for mobile fulfillment users
- Label payload generation and order-status actions
- Feature tests covering API authorization and status transitions

## Risks Or Open Questions

- The project currently centers on web routes and Inertia pages, not a dedicated mobile API surface.
- The right auth model for the mobile app is not yet defined.
- Additional roles or permissions may be needed if fulfillment access should be narrower than full admin access.
- The current order schema may be insufficient for inbound vendor logistics and sticker-print metadata.

## Test Plan

- Add focused feature tests for the API routes once implementation begins
- Verify authorization, order visibility scope, and allowed status transitions

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/core/db-schema.md`

## Completion Notes

Fill this section only when the task is done.
