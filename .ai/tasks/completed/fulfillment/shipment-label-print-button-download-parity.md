# Task: Shipment Label Print Button Download Parity

## Metadata

- Status: completed
- Created: 2026-05-13
- Updated: 2026-05-13
- Source: user request
- Priority: medium

## Raw Request

On the admin/orders/{id} page, in the "Print label" section, "Open printable label" shows the correct label. The "Download PDF label" should download the exact same label as the "Open printable label" as PDF. The "Open printable label" view page has a "Print" button, and it should do the exact same thing as the "Download PDF label" button.

## Objective

Ensure the printable shipment label page uses the same PDF download endpoint exposed from the admin order page, so the label opened in the browser and the downloaded label stay in parity.

## Acceptance Criteria

1. The admin order "Open printable label" route continues rendering the shipment label.
2. The admin order "Download PDF label" route continues downloading the PDF label for the same shipment.
3. The printable label view's "Print" button triggers the same PDF download route used by "Download PDF label".
4. Feature tests cover the printable view's PDF download link.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/order-process.md`

## Likely Implementation Areas

- `app/Http/Controllers/Admin/OrderShipmentLabelController.php`
- `app/Http/Controllers/Api/V1/Admin/OrderShipmentLabelController.php`
- `resources/views/fulfillment/shipment-label.blade.php`
- `tests/Feature/ShipmentLabelRenderingTest.php`

## Risks Or Open Questions

- The mobile API label page also renders the same Blade view, so it should receive a matching PDF URL without changing the API route contract.

## Test Plan

- `php artisan test --compact tests/Feature/ShipmentLabelRenderingTest.php`

## Documentation Updates Required

- None expected.

## Completion Notes

- Passed the shipment PDF download URL into the shared printable label view.
- Changed the label view's visible `Print` action to use the same PDF download endpoint as the admin order page.
- Resized the generated PDF to the same 620px-wide label geometry as the printable browser view.
- Added feature coverage asserting the printable admin label points to the PDF download route.
