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

- [ ] On the admin/orders/{id} page, the 'Courier tracking' should have a dropdown to select the carrier.
    - So we may need a new table for this.
    - The `db.shipments.carrier` column should be `carrier_id` or something similar.
    - The Service level should be belonged to the carrier, and it should be a dropdown as well. So we may need a new table for this.
    - Admin should be able to see all the carriers and services. It should have a CRUD for carrier and service for admin.
- [ ] On the admin/orders/{id} page, in the "Shipment workflow" section
    - It gives an error when trying to change the "Next shipment status" to 'Dispatched' while I don't have added any data in the "Courier tracking" section.
    - The error message is "Select a valid next shipment status." and it's wrong. It should be a meaningful message.
