# Task: Delivery Operations Workflow Planning

## Metadata

- Status: planned
- Created: 2026-04-03
- Updated: 2026-04-03
- Source: user request
- Priority: high

## Raw Request

- Customers buy items online from vendors on `https://loomcraft.work/`.
- The admin manages product creation and management for all vendors.
- Vendors are far from the admin, so vendors send raw products to the admin using public transport.
- The admin performs labeling, packing, and dispatch to customers using a courier service.
- The entire delivery process needs to be planned.
- Questions, unclear points, and suggestions should be surfaced where needed.

## Objective

Define the end-to-end LoomCraft fulfillment workflow from paid order through vendor handoff, inbound receipt by admin, packaging, sticker generation, outbound courier dispatch, and
final delivery confirmation. The output should be a practical operations plan that matches how the business actually runs today and identifies where the website and future mobile
app need to support the process.

## Acceptance Criteria

1. The workflow clearly documents each operational stage from order placement to delivered order.
2. The workflow defines who is responsible at each stage: vendor, admin, courier, or customer-facing system.
3. The workflow identifies order status transitions required to represent the real packing and dispatch process.
4. The workflow captures key operational data needed at each stage, including inbound vendor shipment details and outbound customer shipment details.
5. The workflow includes a list of unresolved business questions that need user confirmation before implementation.
6. The workflow identifies which parts belong in the website admin area versus the future mobile app.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`
- Admin order-management routes, controllers, and pages
- Order status model and workflow rules
- Future inbound/outbound shipment tracking support

## Risks Or Open Questions

- The current order lifecycle may not distinguish vendor-to-admin transit from admin-to-customer courier dispatch.
- The business may need additional statuses such as packed, received_from_vendor, ready_to_ship, or handed_to_courier.
- It is not yet clear whether vendor dispatch details and courier tracking should both be stored in the website.
- Packaging and quality-control checkpoints are described operationally but not yet mapped to system fields.

## Test Plan

- No code tests for planning-only work
- Validate the documented workflow against current order status implementation before any code changes

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
