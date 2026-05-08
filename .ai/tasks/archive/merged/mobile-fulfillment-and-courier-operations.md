# Task: Mobile Fulfillment And Courier Operations

## Metadata

- Status: archived
- Created: 2026-04-23
- Updated: 2026-05-08
- Source: user request
- Priority: high

## Raw Request

- The Android mobile app currently shows only related orders to vendor and admin.
- The mobile app may need improvements to track the full process.
- Admin and vendor need order details that cover fulfillment, shipping, and operational updates.
- Labels and PDFs should be downloadable to mobile app and printable.

## Objective

Define the mobile app support needed for real fulfillment operations. This includes what vendor users, admin users, and possibly delivery or packing operators need to view and
update on mobile for shipment creation, status progression, courier handoff, COD confirmation, complaint intake, return intake, and label/PDF access.

## Acceptance Criteria

1. The task defines mobile roles and permissions for vendor and admin users.
2. The task identifies the order list filters and detail payloads needed on mobile.
3. The task defines the operational actions that must be available in the mobile app.
4. The task identifies which workflows can be read-only at first and which require mutation APIs.
5. The task defines how mobile users access labels, PDFs, and courier tracking links.
6. The task lists unclear UX or permissions questions that need confirmation before implementation.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/planned/mobile/fulfillment-mobile-api-support.md`
- `.ai/tasks/planned/mobile/fulfillment-mobile-app-implementation-brief.md`
- `.ai/tasks/planned/delivery-operations-workflow-planning.md`
- `.ai/tasks/completed/fulfillment/order-details-and-tracking.md`
- `.ai/tasks/completed/mobile/mobile-public-api-v1.md`

## Likely Implementation Areas

- Mobile API resources for order detail and shipment detail
- Vendor-scoped order item and shipment views
- Admin-scoped fulfillment dashboards on mobile
- Action endpoints for packing, dispatch, courier handoff, return receipt, complaint creation, and COD settlement confirmation
- Download endpoints for PDF label and packing documents
- Push notification or polling strategy for courier event updates

## Risks Or Open Questions

- Vendor mobile may need item-level views instead of full-order views when multiple vendors participate in one order.
    - Yes, this is a risk and we need to prioritize this work.
- It is unclear whether the mobile app should support actual printing or only download and open PDFs for external printer apps.
    - Only download and open PDFs for external printer apps.
- It is unclear whether barcode or QR scanning is required to confirm handoff, packing, or return receipt.
    - No need for external devices for scanning. It will change manually.
- It is unclear whether admins need offline-safe queueing for actions taken without network coverage.
    - We need offline-safe queueing for actions taken without network coverage.
- It is unclear whether complaint intake belongs in the same app flow or a separate support workflow.
    - Complaint taken to the website. User should have a place to file a complaint.
- If courier sync is delayed, the app may need to show both platform status and last courier event timestamp explicitly.
    - Yes.

## Test Plan

- No code tests for planning-only work
- Validate the proposed mobile actions against current Android app role boundaries before implementation

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/core/order-process.md`

## Completion Notes

- Archived on 2026-05-08 after backlog reorganization.
- Its scope is now covered by:
  - `.ai/tasks/in-progress/fulfillment/end-to-end-order-fulfillment-platform.md`
  - `.ai/tasks/planned/mobile/fulfillment-mobile-api-support.md`
  - `.ai/tasks/planned/mobile/fulfillment-mobile-app-implementation-brief.md`
