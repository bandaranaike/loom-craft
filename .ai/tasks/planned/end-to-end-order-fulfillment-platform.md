# Task: End To End Order Fulfillment Platform

## Metadata

- Status: planned
- Created: 2026-04-23
- Updated: 2026-05-06
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

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/planned/order-stickers-and-label-printing-requirements.md`
- `.ai/tasks/planned/fulfillment-mobile-api-support.md`
- `.ai/tasks/planned/fulfillment-mobile-app-implementation-brief.md`
- `.ai/tasks/completed/order-details-and-tracking.md`
- `.ai/tasks/completed/admin-orders-and-status-management.md`
- `.ai/tasks/completed/checkout-offline-payment-operations.md`

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
    - `orders.public_id`: keep the existing customer-facing `ORD-...` convention unless production constraints require a forward-only replacement.
    - `invoices.invoice_number`: use a stable platform format such as `INV-YYYYMM-#####`.
    - `shipments.shipment_number`: use a platform-generated format such as `SHP-YYYYMM-#####`.
    - `shipments.tracking_number`: store the courier AWB or courier tracking code and do not synthesize it when a real courier code exists.
- It is still unclear whether one order can generate multiple invoices, or whether invoicing remains strictly one invoice per order.
- It is still unclear whether courier tracking numbers are created only at handoff time or can exist earlier as pre-booked shipments.
- Returns may need separate header and item tables plus a return-shipment tracking number, not just a status on `orders`.
- Complaints may need severity, SLA target, resolution type, and links to refunds, returns, replacements, and courier incidents.

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

## Test Plan

- No code tests for planning-only work
- Validate the child task set against current order and tracking implementation before starting development

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/db-schema.md`[resources](../../../resources)
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
