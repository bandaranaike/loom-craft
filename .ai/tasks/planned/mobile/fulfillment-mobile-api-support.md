# Task: Fulfillment Mobile API Support

## Metadata

- Status: planned
- Created: 2026-04-03
- Updated: 2026-05-08
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

Plan and later implement the phase-2 backend API surface required for full fulfillment operations on mobile. API v1 already exists for login, order list/detail views, basic
status updates, notification token registration, and admin sticker data; this follow-on task should cover shipment-centric workflows, richer fulfillment actions, and operational
payloads that match the evolving end-to-end fulfillment model.

## Acceptance Criteria

1. The task clearly separates already implemented API v1 scope from remaining fulfillment API work.
2. Required mobile shipment/fulfillment endpoints are identified for shipment detail, tracking assignment, dispatch, delivery confirmation, and label/PDF access.
3. The API contract includes the exact shipment and fulfillment data needed for packing and dispatch work.
4. The design accounts for current and future shipment statuses, events, and auditability.
5. The design includes failure states and validation rules for invalid shipment and order transitions.
6. The task identifies whether the phase-2 work extends `/api/v1` or needs versioned follow-on endpoints.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/completed/mobile/mobile-public-api-v1.md`
- `.ai/tasks/in-progress/fulfillment/end-to-end-order-fulfillment-platform.md`

## Likely Implementation Areas

- `routes/api.php`
- Shipment and fulfillment controllers/services
- Shipment resources and validation requests
- Label/PDF download endpoints
- Feature tests covering fulfillment authorization and shipment transitions

## Risks Or Open Questions

- API v1 already exists, so this task should not re-plan login, basic order list/detail, or notification registration.
- The right auth model for expanded fulfillment actions must align with current admin/vendor token scopes.
- Additional roles or permissions may be needed if fulfillment access should be narrower than full admin access.
- Shipment events, proof-of-delivery, and courier assignment workflows are not yet modeled fully enough for the final mobile operational API.

## Test Plan

- Add focused feature tests for new shipment/fulfillment API routes
- Verify authorization, shipment visibility scope, and allowed transition rules

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/core/db-schema.md`

## Completion Notes

API v1 is already completed in `.ai/tasks/completed/mobile/mobile-public-api-v1.md`. This planned task now represents the remaining fulfillment-specific API expansion work only.
