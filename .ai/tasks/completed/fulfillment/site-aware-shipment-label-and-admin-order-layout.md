# Task: Site-Aware Shipment Labels And Admin Order Parcel Controls

## Metadata

- Status: completed
- Created: 2026-07-15
- Updated: 2026-07-15
- Source: user request
- Priority: high

## Raw Request

- Make shipment-label branding follow the active site instead of hardcoding LoomCraft branding.
- Use the active site's return address on shipment labels.
- Support parcels containing multiple items, including item count, styles, and materials.
- Give admins a place on the admin order page to update parcel values used by the label.
- Rebalance the admin order page so the right column does not become much taller than the left.
- Move the Order summary and Uploaded proof of payment sections to the left side.

## Objective

Make fulfillment labels site-aware and accurate for multi-item parcels, expose the operational parcel fields to admins, and improve the admin order detail layout so important order and payment information is balanced on the left column.

## Acceptance Criteria

1. Shipment labels use the active site's logo and display name.
2. Shipment labels use the active site's configured return address.
3. Shipment labels represent all items assigned to a parcel, including item count, styles, and materials.
4. Admins can edit parcel count, weight, dimensions, item count, styles, and materials from the admin order page.
5. Parcel edits are authorized, validated, persisted, and reflected in subsequent labels.
6. The admin order summary and payment proof sections render in the left column.
7. The admin order layout remains usable at mobile and desktop breakpoints.
8. Existing LoomCraft behavior remains the default and existing shipment label routes continue to work.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/in-progress/ui/naturesnature-multi-site-theme-and-deployment.md`

## Likely Implementation Areas

- `config/sites.php`
- `app/Support/Site.php`
- `app/Services/Fulfillment/ShipmentLabelDataBuilder.php`
- `resources/views/fulfillment/shipment-label.blade.php`
- `app/Actions/Order/ShowAdminOrder.php`
- `app/DTOs/Order/AdminOrderSummaryResult.php`
- `app/Http/Controllers/Admin/OrderController.php`
- `app/Http/Requests/Admin/`
- `app/Models/Shipment.php`
- `resources/js/pages/admin/orders/show.tsx`
- `routes/web.php`
- shipment migrations and focused feature tests

## Risks Or Open Questions

- Existing shipments may have no explicit style/material override values; labels need safe derived fallbacks from assigned order items.
- PDF generation currently uses a browser process and may fail in environments where Chromium crashpad is unavailable.

## Test Plan

- Shipment label rendering and data-builder feature tests.
- Admin order parcel update authorization and validation tests.
- Admin order page/Inertia payload tests.
- TypeScript/Vite build and Laravel Pint.

## Documentation Updates Required

- Update `.ai/knowledge/core/db-schema.md` and `.ai/knowledge/core/implementation-status.md` after implementation.

## Completion Notes

Implemented site-aware label branding and return addresses, multi-item parcel derivation and overrides, admin parcel editing, and the rebalanced admin order layout. Focused tests and the Vite build pass; PDF assertions remain dependent on the local Chromium runtime.
