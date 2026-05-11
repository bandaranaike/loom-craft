# Task: End To End Order Fulfillment Platform

## Metadata

- Status: in_progress
- Created: 2026-04-23
- Updated: 2026-05-08
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

### Documentation And Tests

- Core fulfillment docs were updated to reflect the new numbering and shipment/invoice baseline.
- Focused Pest coverage was added for schema, identifier generation, sticker payload data, and tracking-assignment dispatch guards.

## Remaining Work By Track

### Track 1: Fulfillment Workflow And Status Model

- Define the canonical workflow from placed order to delivered/returned/closed.
- Separate order status from shipment status more explicitly. 
- Define allowed transitions for:
  - `order`
  - `payment`
  - `shipment`
  - `return`
  - `complaint`
- Define which actor can perform each transition:
  - admin web
  - vendor mobile
  - admin mobile

### Track 2: Shipment Operations

- Decide whether the current one-shipment-at-placement baseline stays single-package only for phase 1.
- Add shipment event/history tracking. `fulfillment_status_histories` now captures order, payment, shipment, and tracking-assignment history.
- Add courier handoff timestamps and operator attribution.
- Add tracking-number assignment workflow. Admin web assignment is implemented; mobile/API tracking assignment remains to be added if required.
- Add proof-of-delivery fields and evidence handling.
- Add failed-delivery and delivery-exception reasons.

### Track 3: Label And PDF Generation

- Replace the static `bill.html` concept with a server-side label generator.
- Define the final print payload contract for:
  - package label
  - product sticker / packing detail
  - invoice or packing slip if needed
- Add downloadable/printable PDF or HTML endpoints for admin/mobile use.
- Confirm barcode/QR strategy and final print dimensions.

### Track 4: Mobile And Admin Fulfillment Actions

- Expand the mobile API beyond read-only sticker payloads.
- Add safe shipment update actions.
- Add shipment detail payloads for packing/dispatch.
- Add admin web fulfillment screens for:
  - shipment preparation
  - tracking assignment
  - dispatch confirmation
  - delivery confirmation

### Track 5: COD, Returns, And Complaints

- Add COD settlement/remittance tracking.
- Add return request and reverse-logistics records.
- Add complaint workflow with links to order/shipment/return/refund/replacement.
- Add resolution and SLA fields.

### Track 6: Auditability And Multi-Vendor Rules

- Add immutable history for fulfillment state changes.
- Define multi-vendor order handling vs vendor-scoped shipment handling.
- Define multi-package behavior and whether it is phase 1 or later.
- Add operator/action attribution for sensitive fulfillment steps.

## Suggested Next Slices

1. Mobile/API tracking-number assignment if mobile operators need to set AWB values directly.
2. Label/PDF generation service using the new identifier and parcel schema.
3. COD settlement model and admin handling flow.
4. Returns and complaint domain implementation.

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

- It is not yet confirmed whether LoomCraft will support one courier only or multiple couriers with different tracking formats.
    - Only one courier service for now.
- It is not yet clear whether vendors need to see only their own items within a multi-vendor order or a vendor-scoped shipment view generated from the master order.
    - We need to show both.
    - Multivendor order when preparing the order items
    - the vendor scoped the shipment view when the order was dispatched
- COD rules are unclear: whether COD is only a payment method marker or requires settlement tracking against courier remittances.
    - We need settlement tracking
- Complaint handling is unclear: whether complaints are only customer-service cases or need operational links to shipment, return, refund, and replacement flows.
    - need operational links to shipment, return, refund, and replacement flows
- Reverse logistics is unclear: return to admin only, or in some cases return to vendor.
    - return to admin. Admin will communicate with the vendor to determine the return flow.
- Label generation engine is not yet chosen: Blade-to-PDF, headless browser PDF, external document service, or printable HTML rendered by client.
    - Blade-to-PDF. Downloaded PDFs are printed by the admins' phones.
- The current order lifecycle may not cleanly distinguish vendor-to-admin inbound movement from admin-to-customer outbound shipment.
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

## Test Plan

- No code tests for planning-only work
- Validate the child task set against current order and tracking implementation before starting development

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/db-schema.md`[resources](../../../resources)
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
