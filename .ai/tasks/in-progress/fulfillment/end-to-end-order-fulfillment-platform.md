# Task: End To End Order Fulfillment Platform

## Metadata

- Status: in_progress
- Created: 2026-04-23
- Updated: 2026-05-12
- Source: user request
- Priority: high

## Raw Request

- Plan the entire order process from order placement to delivery.
- Include order handling, shipping, parcel tracking, parcel return, cash on delivery, customer complaints, courier sync, order confirmations, payment handling, and tracking.
- The Android mobile app currently shows only related orders for vendor and admin and may need improvements to support the full process.
- New tables and database columns may be needed.
- The current `.ai/knowledge/assets/guidelines/bill.html` label should evolve into a PHP or other server-side service that accepts order details, generates a PDF, and supports
  download and printing from mobile app or web admin portal.
- Create task files and include unclear areas for follow-up answers.

## Objective

Define the full LoomCraft order-operations program across website admin, vendor/admin mobile workflows, database design, courier synchronization, payment and COD handling, label
and PDF generation, delivery confirmation, complaint handling, and reverse-logistics returns. This task is the umbrella planning item that ties the detailed implementation tasks
together.

This merged task now absorbs the earlier workflow-planning and shipment-domain planning items so there is one canonical planning brief for order handling, shipment status design,
delivery operations, returns, complaints, labels, courier tracking, and admin/mobile operational responsibilities.

## Execution Status

- Current state: active umbrella task with foundational fulfillment schema and payload work already implemented.
- Recommended handling: keep this task as the master tracker while delivering the remaining work in focused slices.
- Implementation style: complete one operational slice at a time, with tests and knowledge-doc updates after each slice.

## Confirmed Business Decisions

- Courier:
  - Phase 1 courier is Sri Lanka Post Courier.
  - Courier tracking/AWB numbers are entered manually by admin users now.
  - Mobile tracking entry can be ignored for now because the mobile app is deferred.
  - Courier API synchronization is deferred to a later phase.
- Label and PDF generation:
  - Blade-to-PDF generation is approved.
  - Target print size is a 4x6 thermal label.
  - The generated output should match `.ai/knowledge/assets/guidelines/bill.html` as closely as possible.
  - Both barcode and QR code support are required.
- Mobile app:
  - Mobile app implementation is deferred for now.
  - Mobile API expansion should only be implemented where needed to support already-existing authenticated API label access or future compatibility.
- Order workflow:
  - Confirmed phase 1 lifecycle:
    order placed, payment pending/confirmed, vendor preparing, vendor handed to admin, admin received, quality checked, packed, tracking assigned, dispatched, delivered, closed.
  - This matches the real operational process.
- COD:
  - Courier collects cash, courier remits to LoomCraft/admin, and admin marks COD as settled.
  - Vendor payout depends on COD settlement.
- Returns:
  - Customer requests return, admin approves/rejects, parcel returns to admin, admin checks item, admin contacts vendor, then refund/replacement/close.
  - Return shipping uses the same courier, Sri Lanka Post Courier, in phase 1.
- Complaints:
  - Complaint categories include damaged item, wrong item, late delivery, missing item, payment issue, and refund issue.
  - Complaints may lead to refund, replacement, return, courier claim, or manual resolution.

## Acceptance Criteria

1. The work is broken into focused tasks for workflow design, backend data and API design, mobile operations support, and label/PDF generation.
2. The planning defines the target lifecycle from order creation through delivery, return, refund, or complaint resolution.
3. The plan identifies the core identifiers that must exist for order, invoice, shipment, and courier tracking.
4. The plan identifies required backend schema changes and operational audit/history needs.
5. The plan identifies where admin web, vendor mobile, and admin mobile responsibilities differ.
6. All known unclear business decisions are listed explicitly so they can be answered before implementation.
7. The workflow clearly documents vendor-to-admin inbound handling, admin packing, courier handoff, and final customer delivery confirmation.
8. The plan defines canonical status transitions for `order`, `payment`, `shipment`, `return`, and `complaint` records.
9. The plan identifies the minimum shipment, tracking-event, return, and complaint tables, columns, indexes, and foreign keys.
10. The plan defines minimum audit-history requirements so every major operational state change is traceable.
11. The plan identifies how multi-vendor, multi-package, and partial-shipment scenarios should be represented, even if some are deferred to a later phase.
12. The plan clearly separates what belongs in the current web admin, what belongs in mobile operations, and what can remain manual at first.
13. The plan distinguishes identifiers and parcel attributes that already exist in the codebase from those that still need to be added.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/skills.md`
- 
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/planned/fulfillment/order-stickers-and-label-printing-requirements.md`
- `.ai/tasks/planned/mobile/fulfillment-mobile-api-support.md`
- `.ai/tasks/planned/mobile/fulfillment-mobile-app-implementation-brief.md`
- `.ai/tasks/completed/fulfillment/order-details-and-tracking.md`
- `.ai/tasks/completed/fulfillment/admin-orders-and-status-management.md`
- `.ai/tasks/completed/commerce/checkout-offline-payment-operations.md`

## Likely Implementation Areas

- Order, payment, shipment, and complaint domain models
- New shipment, courier-event, return, and complaint tables
- Admin fulfillment UX and mobile app operational screens
- Courier-facing synchronization jobs and webhook handlers
- Server-side label and PDF generation service
- Download/print endpoints for web admin and mobile apps
- Operational workflow documentation for vendor handoff, inbound receiving, packing, quality check, stickering, dispatch, failed delivery, and return intake
- Identifier and numbering conventions for order, invoice, shipment, return, and complaint records
- Status transition rules, audit trail support, and future mobile payload contracts

## Current System Baseline

- `orders.public_id` already exists and is the current customer-facing order reference. It is generated as an opaque `ORD-...` token and is already used in routes, order pages, and mobile/API payloads.
- `shipments.tracking_number` already exists as a nullable shipment field and is the correct place to store the courier AWB or courier tracking code.
- `shipments.carrier`, `shipments.status`, `shipments.shipped_at`, and `shipments.delivered_at` already exist.
- A dedicated `invoices` table now exists with immutable `invoice_number` generation and one invoice record per order in the current implementation.
- Product catalog records already store `dimension_length`, `dimension_width`, `dimension_height`, and `dimension_unit`.
- Shipment records now store `shipment_number`, `service_level`, `package_count`, `parcel_weight`, `weight_unit`, `parcel_length`, `parcel_width`, `parcel_height`, and `parcel_dimension_unit`.
- The current system still does not store product dead weight.
- The admin/mobile sticker payload now exposes order number, invoice number, shipment number, tracking number, carrier/service, shipment parcel metrics, and product dimensions.

## Started Implementation Slice

- Implemented operational `orders.order_number` generation alongside the existing customer-facing `orders.public_id`.
- Implemented automatic one-to-one invoice creation with immutable `invoices.invoice_number`.
- Implemented `shipments.shipment_number` and shipment-level parcel metric fields needed for labels.
- Implemented automatic initial shipment creation during order placement.
- Extended the sticker payload contract so label/PDF work can consume the new identifiers and parcel fields.
- Added focused Pest coverage for schema presence, identifier generation, invoice creation, shipment numbering, and sticker payload exposure.
- Added admin courier tracking assignment with immutable fulfillment history and dispatch guarded until carrier/tracking data exists.
- Added server-rendered shipment label generation for admin web and authenticated mobile/API print flows.
- Added explicit phase 1 shipment workflow statuses and checkpoint timestamps for vendor preparing, vendor handoff to admin, admin receiving, quality check, packing, dispatch, and delivery.
- Added admin order-page fulfillment timeline visibility for those checkpoint timestamps.
- Added Blade-to-PDF 4x6 shipment label downloads for admin web and authenticated mobile/API label access.
- Added real SVG Code 128 barcodes and SVG QR code generation for the shipment label.

## Completed So Far

### Data And Identifier Foundation

- `orders.public_id` remains the customer-facing opaque identifier.
- `orders.order_number` now exists for operational use.
- `invoices` now exists as a one-to-one order document table.
- `invoices.invoice_number` now exists and is generated automatically.
- `shipments.shipment_number` now exists and is generated automatically.
- Shipment parcel fields now exist for package count, weight, and packed dimensions.

### Order Placement Baseline

- Order placement now creates:
  - the `orders` record
  - the linked `invoice`
  - the initial `shipment`
  - the `payment`
  - the `order_items`
  - the `order_addresses`
- The initial shipment is created with `pending` status and no tracking number.

### Label Data Baseline

- Admin/mobile sticker payloads now include:
  - `order_number`
  - `invoice_number`
  - `shipment_number`
  - courier tracking/carrier/service fields
  - parcel metrics
  - product dimensions from the catalog

### Shipment Tracking Assignment

- Admin web users can assign carrier, service level, and courier tracking/AWB to a shipment.
- Tracking assignment records an immutable `fulfillment_status_histories` entry with `reason=tracking_updated`.
- Shipments cannot transition from `ready_for_dispatch` to `dispatched` until carrier and tracking number are assigned.

### Label And Print Rendering

- The static `bill.html` concept now has a server-side Blade rendering path backed by live order, invoice, shipment, address, parcel, and product data.
- Admin web users can open a printable shipment label from the admin order page.
- Admin web users can download a 4x6 PDF shipment label from the admin order page.
- Authenticated mobile/API users with `stickers:read` can render the same print-ready shipment label over the API.
- Authenticated mobile/API users with `stickers:read` can download the same 4x6 PDF shipment label over the API.
- Native binary PDF generation is implemented with Blade-to-PDF, Code 128 barcodes, QR code output, and the PNG assets from `.ai/knowledge/assets/guidelines/bill.html`.
- PDF rendering requires the PHP GD extension because Dompdf needs it to embed the PNG logo and handling icons from the source label design.

### Documentation And Tests

- Core fulfillment docs were updated to reflect the new numbering and shipment/invoice baseline.
- Focused Pest coverage was added for schema, identifier generation, sticker payload data, and tracking-assignment dispatch guards.
- Focused Pest coverage now verifies the confirmed admin fulfillment checkpoint workflow, invalid skipped transitions, schema support for checkpoint timestamps, and mobile/API compatibility with the updated first vendor status.
- Focused Pest coverage now verifies HTML label rendering, admin PDF downloads, authenticated mobile/API PDF downloads, barcode/QR output, and sticker-scope authorization.

## Remaining Work By Track

### Track 1: Fulfillment Workflow And Status Model

- Shipment-side phase 1 lifecycle is now implemented for vendor preparing, vendor handed to admin, admin received, quality checked, packed, tracking assigned, dispatched, delivered, and automatic order fulfillment on delivery.
- Order-side payment pending/confirmed, fulfilled, closed, and cancelled statuses already exist.
- Separate order status from shipment status more explicitly. 
- Define allowed transitions for:
  - `order` beyond the current pending/paid/confirmed/fulfilled/closed/cancelled baseline
  - `payment` beyond the current pending/collection_pending/paid/failed/refunded baseline
  - `return`
  - `complaint`
- Define which actor can perform each transition:
  - vendor mobile
  - admin mobile

### Track 2: Shipment Operations

- Decide whether the current one-shipment-at-placement baseline stays single-package only for phase 1.
- Add shipment event/history tracking. `fulfillment_status_histories` now captures order, payment, shipment, and tracking-assignment history.
- Courier handoff and operational checkpoint timestamps now exist through shipment fields; operator attribution exists through `fulfillment_status_histories`.
- Admin web tracking-number assignment is implemented.
- Mobile/API tracking assignment is deferred because mobile app work is currently skipped.
- Add proof-of-delivery fields and evidence handling.
- Add failed-delivery and delivery-exception reasons.
- Model Sri Lanka Post Courier as the phase 1 courier, with manually entered AWB/tracking numbers.
- Defer courier API synchronization to a later phase.

### Track 3: Label And PDF Generation

- Replace the static `bill.html` concept with a server-side label generator. Implemented.
- Implement Blade-to-PDF generation for the existing label template. Implemented.
- Match `.ai/knowledge/assets/guidelines/bill.html` as closely as possible. Implemented using the source PNG assets and matching layout/styles.
- Target a 4x6 thermal label layout. Implemented.
- Add barcode and QR code support. Implemented with SVG Code 128 barcodes and SVG QR codes.
- Add downloadable PDF endpoints for admin use. Implemented for admin web and authenticated mobile/API label access.
- Barcode encoding:
  - Main barcode encodes the courier tracking/AWB value.
  - Order barcode encodes the order number.
  - Invoice barcode encodes the invoice number.
  - QR code encodes the public order tracking URL when available, otherwise shipment/tracking fallback data.

### Track 4: Mobile And Admin Fulfillment Actions

- Mobile app implementation is deferred for now.
- Do not prioritize mobile-only fulfillment actions in the next slice.
- Keep existing authenticated mobile/API label access working.
- Add admin web fulfillment screens for:
  - shipment preparation
  - tracking assignment
  - dispatch confirmation
  - delivery confirmation

### Track 5: COD, Returns, And Complaints

- Add COD settlement/remittance tracking for courier-collected cash. Implemented on `payments` with collected/remitted amounts, remittance reference, settlement note, settled-by user, and settled-at timestamp.
- Gate vendor payout eligibility on COD settlement where the order uses COD. Implemented in `VendorPayout::isEligibleForPayment()` and enforced when marking COD payouts as paid.
- Add return request and reverse-logistics records for returns routed back to admin.
- Support Sri Lanka Post Courier as the return courier for phase 1.
- Add complaint workflow with links to order/shipment/return/refund/replacement and courier claims.
- Add resolution and SLA fields.

### Track 6: Auditability And Multi-Vendor Rules

- Add immutable history for fulfillment state changes.
- Define multi-vendor order handling vs vendor-scoped shipment handling.
- Define multi-package behavior and whether it is phase 1 or later.
- Add operator/action attribution for sensitive fulfillment steps.

## Suggested Next Slices

1. Implement returns routed back to admin, using Sri Lanka Post Courier for phase 1 return shipping.
2. Implement complaint handling with links to shipment, return, refund, replacement, courier claim, or manual resolution.
3. Revisit mobile app and mobile-only fulfillment actions after the admin web process is stable.

## Recommended Identifier Standards

- Order number:
  Keep the existing `orders.public_id` for customer lookup and sharing because it is already live and integrated.
  Add a separate human-friendly operational `orders.order_number` for admin/vendor documents and printed labels in a sequential format such as `ORD-202605-000125`.
- Invoice number:
  Introduce a dedicated immutable invoice number such as `INV-202605-000125`.
  Invoice numbering should be sequential, finance-friendly, and independent from the opaque order public ID.
- Tracking number:
  Use the real courier-provided AWB/tracking code in `shipments.tracking_number`.
  Do not synthesize values like `TRK-9482-5517` in production unless a courier integration explicitly requires an internal pre-allocation placeholder before handoff.
- Shipment number:
  Add a platform shipment identifier such as `SHP-202605-000125` for internal operations, because one order may later map to more than one shipment.

## Recommended Label Example Conventions

- `Order No` on the shipping label should use the operational order number once added, for example `ORD-202605-000125`.
- `Tracking No` should use the real courier AWB/tracking code, for example `7734567890` or another courier-issued format. It should not include a fake `TRK-` prefix unless that prefix is actually part of the courier code.
- `Invoice No` should use the finance document number, for example `INV-202605-000125`.
- If the operational order number is not implemented yet, the temporary fallback for labels should be the existing `orders.public_id`.

## Parcel Data Assessment

- Already available now:
  customer shipping address
  customer phone
  ordered products
  product code
  quantity
  product dimensions from the catalog (`dimension_length`, `dimension_width`, `dimension_height`, `dimension_unit`)
- Not available yet and should be planned explicitly:
  product dead weight
  guaranteed tracking number assignment workflow
  courier booking / handoff timestamps beyond the current shipment status timestamps
  proof-of-delivery evidence payload
  return-shipment identifiers and parcel metrics

## Label Planning Notes

- Product dimensions should not be assumed to equal the final courier parcel dimensions. The task should plan shipment-level measured dimensions captured during packing.
- Weight on the label should come from a shipment/package measurement field, not from a hardcoded example and not from product data unless there is an explicit fallback rule.
- For multi-item or multi-vendor orders, parcel details must belong to the shipment/package record, not only to the order header.

## Risks Or Open Questions

- Courier selection is resolved for phase 1:
    - Use Sri Lanka Post Courier only.
    - Admin users enter AWB/tracking numbers manually.
    - Courier API synchronization is deferred.
- Multi-vendor visibility is resolved at the business level:
    - Show multi-vendor order preparation views while preparing order items.
    - Show vendor-scoped shipment views after dispatch.
    - Detailed schema/API representation still needs implementation.
- COD is resolved at the business level:
    - COD requires settlement tracking against courier remittances.
    - Vendor payout depends on COD settlement.
- Complaint handling is resolved at the business level:
    - Complaints need operational links to shipment, return, refund, replacement, courier claim, and manual resolution flows.
- Reverse logistics is resolved at the business level:
    - Returns go back to admin.
    - Admin communicates with the vendor to decide the final return outcome.
    - Sri Lanka Post Courier is used for return shipping in phase 1.
- Label generation is resolved at the business level:
    - Use Blade-to-PDF.
    - Target 4x6 thermal labels.
    - Match `.ai/knowledge/assets/guidelines/bill.html` as closely as possible.
    - Include both barcode and QR code support.
- Mobile app implementation is intentionally deferred for now.
- The current order lifecycle still needs implementation work to cleanly distinguish vendor-to-admin inbound movement from admin-to-customer outbound shipment.
- Packaging and quality-control checkpoints are still operational concepts and must be mapped into concrete statuses, timestamps, and user actions.
- Suggested identifier baseline:
    - `orders.public_id`: keep the existing customer-facing opaque `ORD-...` convention for lookup and sharing.
    - `orders.order_number`: add a human-friendly operational format such as `ORD-YYYYMM-#####`.
    - `invoices.invoice_number`: use a stable finance format such as `INV-YYYYMM-#####`.
    - `shipments.shipment_number`: use a platform-generated format such as `SHP-YYYYMM-#####`.
    - `shipments.tracking_number`: store the courier AWB or courier tracking code and do not synthesize it when a real courier code exists.
- One order should have exactly one invoice.
- The system should auto-create the initial shipment record during order placement.
- Courier tracking numbers are still expected to be assigned later in the fulfillment flow unless courier pre-booking is introduced.
- Returns may need separate header and item tables plus a return-shipment tracking number, not just a status on `orders`.
- Complaints may need severity, SLA target, resolution type, and links to refunds, returns, replacements, and courier incidents.
- Complaint categories to support include damaged item, wrong item, late delivery, missing item, payment issue, and refund issue.
- Shipment label parcel metrics currently have a data gap:
    - product dimensions exist today
    - shipment/package dimensions now exist at shipment level
    - shipment/package weight now exists at shipment level
    - product dead weight still does not exist
    - invoice number now exists

## Proposed Scope Structure

1. Phase 1 planning output

- Canonical workflow from payment confirmation through delivery, return, refund, or complaint closure.
- Canonical status map for `order`, `shipment`, `payment`, `return`, and `complaint`.
- Core schema proposal for shipments, shipment events, COD remittances, returns, and complaints.
- Admin web and mobile responsibility split.

2. Likely follow-up implementation tracks

- Shipment domain model and status workflow implementation.
- Delivery operations admin UX and mobile API support.
- Label/PDF generation pipeline.
- Courier synchronization and tracking ingestion.
- Complaint, return, and COD settlement handling.

Current priority adjustment:

- Admin web fulfillment, PDF labels, COD settlement, returns, and complaints should be completed before mobile app implementation.
- Courier synchronization and tracking ingestion are deferred because Sri Lanka Post Courier tracking is entered manually in phase 1.

3. Suggested additions not yet explicit in older tasks

- Define proof-of-delivery evidence requirements: delivery timestamp, recipient name, courier proof reference, and failed-delivery reason.
- Define exception flows: damaged parcel, lost parcel, customer unreachable, address issue, customer refusal, and partial fulfillment.
- Define operational SLAs for vendor handoff, admin packing, dispatch lead time, complaint response, and return processing.
- Define immutable history requirements for status transitions and operator attribution.

## Progress Notes

- 2026-05-08:
  Implemented the fulfillment foundation layer:
  order number, invoice table/number, shipment number, shipment parcel fields, automatic shipment creation on order placement, and richer sticker payload support.
  This task remains open because the operational workflow, courier actions, returns, complaints, and label/PDF pipeline are still pending.
- 2026-05-12:
  Confirmed phase 1 business decisions:
  Sri Lanka Post Courier only, manual AWB/tracking entry, courier API later, Blade-to-PDF approved, 4x6 thermal labels, barcode and QR required, mobile app deferred, COD settlement required before vendor payout, returns route back to admin through the same courier, and complaints may resolve through refund, replacement, return, courier claim, or manual handling.
- 2026-05-12:
  Implemented the first remaining slice:
  explicit shipment statuses for vendor preparing, vendor handoff to admin, admin received, quality checked, packed, ready for dispatch, dispatched, and delivered; shipment checkpoint timestamps; admin order-page timeline visibility; and tests for valid and invalid workflow transitions.
- 2026-05-12:
  Implemented the label/PDF slice:
  added Blade-to-PDF dependencies, 4x6 PDF download endpoints for admin web and authenticated mobile/API label access, SVG Code 128 barcode generation, SVG QR generation, bill.html PNG asset embedding, and focused PDF rendering tests. PHP GD was installed because Dompdf requires it for PNG assets.
- 2026-05-13:
  Implemented COD settlement/remittance tracking and vendor payout dependency:
  admin COD settlement now requires a remitted amount matching the payment amount before moving COD to paid; payments store COD collected/remitted amounts, remittance reference, settlement note, settled-by user, and settled-at timestamp; COD vendor payouts are blocked from `paid` status until settlement exists.
- 2026-05-13:
  Future Codex work should use the lean workflow by default:
  inspect only task-relevant files, avoid broad repository scans unless needed, make the smallest coherent slice, run focused tests first, run broader checks only when the changed surface requires them, and update only the documents touched by the work.

## Test Plan

- No code tests for planning-only work
- Validate the child task set against current order and tracking implementation before starting development

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/db-schema.md`[resources](../../../resources)
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
