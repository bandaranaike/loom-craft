# Task: Carrier And Service Management For Shipments

## Metadata

- Status: completed
- Created: 2026-05-13
- Updated: 2026-05-13
- Source: user request
- Priority: high

## Raw Request

From `.ai/inbox/task-request.md`:

- On the `admin/orders/{id}` page, the "Courier tracking" section should have a dropdown to select the carrier.
  - This may need a new table.
  - The `shipments.carrier` column should become `carrier_id` or similar.
  - Service level should belong to the carrier and should also be a dropdown.
  - Admin should be able to see all carriers and services, with CRUD for carrier and service.
- On the `admin/orders/{id}` page, in the "Shipment workflow" section:
  - Changing "Next shipment status" to `Dispatched` without Courier tracking data currently shows "Select a valid next shipment status."
  - That message is wrong and should be meaningful.

## Objective

Normalize shipment carriers and carrier service levels into admin-managed records, update admin order tracking controls to use dropdowns, and provide a clear validation message when dispatch is blocked because tracking details are missing.

## Acceptance Criteria

1. Admins can create, edit, list, and delete carriers.
2. Admins can create, edit, list, and delete service levels belonging to a carrier.
3. Admin order "Courier tracking" uses carrier and service dropdowns instead of free-text carrier/service fields.
4. Shipment records store carrier and service level references while preserving existing tracking numbers.
5. Dispatch without required tracking data shows a meaningful error that explains tracking details are required before dispatch.
6. Existing shipment label generation still receives carrier and service level display values.
7. Focused feature tests cover carrier/service CRUD, tracking assignment, and dispatch validation messaging.

## Relevant Knowledge To Read

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `database/migrations/*shipments*`
- `app/Models/Shipment.php`
- New carrier and service-level models/migrations/controllers/requests
- `routes/web.php`
- `app/Http/Controllers/Admin/OrderController.php`
- `app/Http/Requests/Admin/UpdateShipmentTrackingRequest.php`
- `app/Services/Fulfillment/FulfillmentStatusService.php`
- `app/Actions/Order/ShowAdminOrder.php`
- `resources/js/pages/admin/orders/show.tsx`
- New or existing admin carrier/service pages
- Focused Pest feature tests

## Risks Or Open Questions

- Existing shipment rows have string carrier/service values; the migration needs a conservative conversion path.
- Delete behavior must avoid breaking shipments already linked to a carrier or service.
- Admin navigation placement for carrier/service CRUD should follow existing sidebar conventions.

## Test Plan

- Run focused carrier/service CRUD tests.
- Run focused admin order shipment tracking/status tests.
- Run TypeScript check for changed Inertia pages.
- Run Pint on dirty PHP files.

## Documentation Updates Required

- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/skills.md` only if lean Codex workflow guidance needs tightening.

## Completion Notes

Implemented on 2026-05-13.

- Added `shipping_carriers` and `shipping_services`.
- Added shipment references `shipping_carrier_id` and `shipping_service_id` while keeping carrier/service display snapshots for existing labels and API responses.
- Added admin carrier/service CRUD page at `/admin/shipping-carriers`.
- Added admin sidebar navigation for Shipping Carriers.
- Updated admin order Courier tracking fields to carrier/service dropdowns.
- Updated shipment dispatch validation to show a meaningful tracking-required message.
- Verified with focused carrier/service and shipment tracking tests, TypeScript check, frontend build, Pint, and local migration.
