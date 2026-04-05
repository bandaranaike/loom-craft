# Task: Mobile Public API v1

## Metadata

- Status: in-progress
- Created: 2026-04-05
- Updated: 2026-04-05
- Source: user request
- Priority: high

## Raw Request

- We have to create public API. the details are in the file `.ai/inbox/api-spec.md`

## Objective

Implement a JSON API surface under `/api/v1` for the LoomCraft admin/vendor mobile application, covering token-based login, scoped order listing/detail views, order status updates, notification token registration, and admin sticker data, while aligning the implementation with the current Laravel codebase and actual order workflow.

## Acceptance Criteria

1. A versioned JSON API route surface exists for the mobile app under `/api/v1`.
2. Authentication returns a bearer token and authenticated user payload for admin and approved vendor users.
3. Admin and vendor order list endpoints return only the data each role is allowed to see.
4. Admin and vendor order detail endpoints enforce role-based visibility of customer, payment, and address information.
5. Order status update rules match the current application workflow and reject invalid transitions.
6. Notification token registration persists the token against the authenticated user.
7. Admin sticker-data endpoint returns dense shipping payload data for an order.
8. Focused feature tests cover authentication, authorization, scoped order visibility, and status updates.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/inbox/api-spec.md`

## Likely Implementation Areas

- `bootstrap/app.php`
- `routes/api.php`
- API auth setup and token issuance
- API controllers, resources, and form requests
- Order policies and supporting actions
- Notification token persistence
- `tests/Feature/*` API coverage

## Risks Or Open Questions

- The current app does not yet include Laravel Sanctum or an API route surface.
- The API spec's vendor status transitions do not match the current application behavior.
- The API spec references notification token registration, but no existing FCM token persistence exists.
- The API spec points to `.ai/resources/db-schema.md`, but the maintained schema file is `.ai/knowledge/core/db-schema.md`.

## Test Plan

- Add focused Pest feature tests for API login, order list/detail access, status updates, and notification token registration
- Run only the affected API-related tests with `php artisan test --compact`

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/core/db-schema.md`

## Completion Notes

Fill this section only when the task is done.
