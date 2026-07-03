# Task: Browser Local Timezone Order Times

## Metadata

- Status: completed
- Created: 2026-07-03
- Updated: 2026-07-03
- Source: user request
- Priority: high

## Raw Request

I found some fixes required in the application. I can see some timezone issue in several places on the front end. Specially order times. The database should be saved all the dates in UTC. But when showing in the browser it should be showing the date and time to compatible with the actual current timezone of the browser or the client device.

## Objective

Ensure order-related dates remain stored and transported as UTC timestamps, while all browser-facing date and time displays render in the client's local timezone consistently across customer, vendor, and admin order experiences.

## Acceptance Criteria

1. Order date/time values shown in browser-rendered pages are converted from UTC to the browser or client device timezone before display.
2. UTC persistence remains unchanged for database writes and backend order timestamps.
3. Shared frontend formatting is used where practical so order time displays are consistent across customer, vendor, and admin pages.
4. Focused tests or type checks cover the changed behavior.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/order-process.md`

## Likely Implementation Areas

- `resources/js/pages/**/orders/**/*.tsx`
- `resources/js/lib/**`
- `app/DTOs/Order/**`
- order-related feature tests or frontend type checks

## Risks Or Open Questions

- Some timestamp strings may be emitted without timezone offsets, which browsers can misinterpret as local time instead of UTC.
- The issue may exist outside order pages, but order-facing screens are the priority from the raw request.

## Test Plan

- Inspect current order DTO timestamp serialization.
- Add or update focused coverage for UTC timestamp serialization if backend changes are needed.
- Run TypeScript checks for changed React/Inertia pages or shared frontend helpers.
- Run focused Pest tests for affected order DTO or controller behavior if PHP changes are made.

## Documentation Updates Required

- None expected.

## Completion Notes

- Completed on 2026-07-03.
- Replaced order-facing `toDateTimeString()` serialization with timezone-aware ISO-8601 strings for customer, dashboard, vendor, and admin order DTO payloads.
- Added a shared browser-local date/time formatter in `resources/js/lib/dates.ts`.
- Updated order history, order detail, order confirmation, dashboard order history, vendor order, admin order, shipment timeline, and payment proof displays to format timestamps in the client runtime timezone.
- Verified with:
  - `vendor/bin/pint --dirty --format agent`
  - `pnpm exec prettier --write ...`
  - `php artisan test --compact tests/Feature/OrderHistoryTest.php tests/Feature/OrderOperationsTest.php`
  - `pnpm run types`
