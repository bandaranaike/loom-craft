# Task: Order Stickers And Label Printing Requirements

## Metadata

- Status: planned
- Created: 2026-04-03
- Updated: 2026-04-03
- Source: user request
- Priority: high

## Raw Request

- One package should support sticker printing.
- The current requested sticker content is:
  - customer delivery details and address
  - product details with product name and price
  - order details with order number
- Sticker contents may be adjusted if a better structure is identified.
- The printer should be handled by a mobile app.

## Objective

Define the sticker and label system needed for LoomCraft package fulfillment, including what labels are required per order, the exact data each label needs, how labels should be grouped for printing, and what output format the future mobile app and website API must support.

## Acceptance Criteria

1. The requirement defines the minimum sticker set needed for a package.
2. Each sticker type has a clear data contract including required and optional fields.
3. The requirement recommends whether the current three-sticker approach should be kept, merged, or expanded.
4. The requirement identifies formatting needs such as barcode/QR code support, print dimensions, and printer compatibility assumptions.
5. The requirement defines where label data should come from in the website domain model.
6. The requirement identifies any missing backend data that must be added before printing can be implemented.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- Orders, order addresses, order items, and product payload shaping
- Future label-generation endpoint or document-generation service
- Future mobile app print workflow
- Admin fulfillment UX and print trigger points

## Risks Or Open Questions

- Printer model, paper size, and connection method are not yet specified.
- It is not yet clear whether stickers should include branding, QR codes, or courier metadata.
- Multi-item orders may require more than one product sticker or a grouped packing slip approach.
- The app may need offline-safe printable payloads depending on printer connectivity.

## Test Plan

- No code tests for requirements-only work
- Validate label fields against current order, product, and address data before implementation

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/db-schema.md`

## Completion Notes

Fill this section only when the task is done.
