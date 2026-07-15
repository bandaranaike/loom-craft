# Freeform Task Request

Write the task here in your own way.

No structure is required.

Useful things to include when available:

- What you want changed
- Why it matters
- Screens, routes, or files involved
- Expected behavior
- Constraints or things to avoid
- Any examples

Latest note:

- [ ] Following issue, I found
    - Mainly app/Services/Fulfillment/ShipmentLabelDataBuilder.php and resources/js/layouts/public-site-layout.tsx files are involved
    - In the resources/js/layouts/public-site-layout.tsx page
        - In line 543: The logo should be changes with the site (loomcraft of naturesnature)
        - In line 545: The brand-name should not be hardcoded as "LOOMCRAFT". It should be change with the site change(Loomcraft of Naturesnature)
    - In the file app/Services/Fulfillment/ShipmentLabelDataBuilder.php
        - In line 50: The return address should be change with the site change(Loomcraft of Naturesnature)
        - One paracel may contain multiple items. So, the number of items and styels, materials should be change.
        - There should be a place to update those above parcel values in resources/js/pages/admin/orders/show.tsx page. Controller will generate them, but admin shoudl be able to change
          them.
- [ ] In the resources/js/pages/admin/orders/show.tsx page,
  - Right hand side height is going multiple times than the left hand side height.
  - The "Order summary" section and "Uploaded proof of payment" section can be moved to the left side.
