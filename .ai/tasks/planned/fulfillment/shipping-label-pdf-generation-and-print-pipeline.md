# Task: Shipping Label PDF Generation And Print Pipeline

## Metadata

- Status: completed
- Created: 2026-04-23
- Updated: 2026-05-12
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

- Implemented baseline:
  - `bill.html` is represented by the Laravel Blade view at `resources/views/fulfillment/shipment-label.blade.php`.
  - The label uses structured shipment data from `App\Services\Fulfillment\ShipmentLabelDataBuilder`.
  - PDFs are generated on demand using `barryvdh/laravel-dompdf`.
  - The phase 1 print size is 4x6 thermal.
  - Barcode support uses SVG Code 128 output from `picqer/php-barcode-generator`.
  - QR support uses SVG output from `endroid/qr-code`.
  - The main tracking barcode encodes the courier tracking/AWB value.
  - The order barcode encodes the order number.
  - The invoice barcode encodes the invoice number.
  - The QR code encodes the public order tracking URL when available, otherwise shipment/tracking fallback data.
  - Admin web and authenticated mobile/API PDF download endpoints are implemented.
- Deferred:
  - Persistent document caching/versioning.
  - Immutable generated-document records after courier handoff.
  - Return labels and packing slips as separate document types.
  - Direct native mobile printer SDK integration.
- Infrastructure note:
  - PHP GD is required because Dompdf needs it to embed the PNG logo and handling icons from `.ai/knowledge/assets/guidelines/bill.html`.

## Test Plan

- `php artisan test --compact tests/Feature/ShipmentLabelRenderingTest.php`
- `php artisan test --compact tests/Feature/ShipmentLabelRenderingTest.php tests/Feature/ShipmentTrackingAssignmentTest.php tests/Feature/OrderOperationsTest.php tests/Feature/FulfillmentStatusWorkflowTest.php`
- `pnpm run types`
- `composer audit`

## Documentation Updates Required

- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Completed on 2026-05-12.

Implemented Blade-to-PDF 4x6 shipment labels matching `.ai/knowledge/assets/guidelines/bill.html` as closely as practical with the existing live order/shipment payload. Added admin web and authenticated mobile/API PDF download endpoints, real SVG barcode/QR generation, and tests for HTML rendering, PDF rendering, and authorization.
