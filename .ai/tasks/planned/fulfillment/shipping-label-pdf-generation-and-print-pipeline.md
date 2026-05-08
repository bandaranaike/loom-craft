# Task: Shipping Label PDF Generation And Print Pipeline

## Metadata

- Status: planned
- Created: 2026-04-23
- Updated: 2026-05-08
- Source: user request
- Priority: high

## Raw Request

- The current `.ai/knowledge/assets/guidelines/bill.html` should evolve into a PHP file or some other server-side service.
- It should accept order details and generate a PDF.
- The PDF should be downloadable from mobile app or web admin portal and printable.
- The label structure may be changed to align with the new order workflow requirements.

## Objective

Define and then implement a proper server-side shipping-document pipeline for LoomCraft. The current standalone HTML label should become a maintainable template-driven system that receives normalized order, shipment, customer, return-address, and courier data and produces printable documents such as shipping labels, packing slips, and later return labels.

## Acceptance Criteria

1. The task defines the target server-side rendering approach for labels and PDFs.
2. The task defines the canonical payload required to generate a shipping label.
3. The task defines the storage, caching, regeneration, and download behavior for generated PDFs.
4. The task identifies separate document types needed now and later.
5. The task defines web admin and mobile download/print entry points.
6. The task lists all unresolved formatting and infrastructure questions before implementation starts.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/tasks/planned/fulfillment/order-stickers-and-label-printing-requirements.md`
- `.ai/tasks/in-progress/fulfillment/end-to-end-order-fulfillment-platform.md`
- `.ai/knowledge/assets/guidelines/bill.html`
- `.ai/knowledge/assets/guidelines/loom_craft_label_desing_docs.md`
- `.ai/knowledge/assets/guidelines/loom_craft_label_system_docs.md`

## Likely Implementation Areas

- New Blade or PHP view template for shipping label rendering
- Dedicated service object for label data assembly
- PDF generation service or headless browser rendering layer
- Barcode and QR generation support
- Secure download endpoint for admin web and mobile app
- Optional persistent document table such as `generated_documents` if caching/versioning is needed
- Future print job integration hooks

## Risks Or Open Questions

- Recommended baseline approach: move `bill.html` into a Laravel view or dedicated document template and render with structured shipment data before converting to PDF.
- Identifier and parcel-data foundations are now in place, so the remaining uncertainty is about rendering approach, caching/versioning, and document-type separation rather than missing core order/shipment keys.
- It is unclear whether PDFs should be generated on demand every time or cached per shipment version.
- It is unclear whether documents must be immutable once a shipment is handed to the courier.
- It is unclear which paper sizes and printers are target devices: A4, 4x6 thermal, or both.
- It is unclear whether branding-heavy labels and courier-compliant labels are the same document or two separate document types.
- It is unclear whether order barcode, invoice barcode, and tracking barcode all need machine-readable standards like Code128.
- It is unclear whether the mobile app needs direct print support or only file download/open behavior.

## Test Plan

- No code tests for planning-only work
- Before implementation, validate required fields against current order, address, product, and shipment data sources

## Documentation Updates Required

- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
