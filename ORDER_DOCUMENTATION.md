# Technical Documentation: Order Management System

## Overview
The Order Management System (OMS) in Loom Craft is a multi-role platform (Admin, Vendor, Customer) designed to handle the full lifecycle of an order, from placement to delivery. It features a robust state machine for fulfillment, integrated payments (LKR-based), and automated document generation.

## Data Model

### Core Entities
- **Order (`App\Models\Order`):** The central entity representing a customer purchase.
    - `public_id`: Opaque customer-facing lookup key (e.g., `ORD-ABC123...`) used in URLs.
    - `order_number`: Operational/human-friendly document number (e.g., `ORD-202605-000123`).
    - `status`: Current state of the order (see State Management).
    - `currency`: Canonical in **LKR**.
    - `shipping_responsibility`: Either `vendor` or `platform`.
- **OrderItem (`App\Models\OrderItem`):** Individual products within an order.
    - `unit_price`: Selling price at time of purchase.
    - `commission_rate`: Platform commission (defaults to **7.00%**).
    - `commission_amount`: Calculated as `unit_price * quantity * (commission_rate / 100)`.
- **OrderAddress (`App\Models\OrderAddress`):** Shipping and billing snapshots. Billing is mirrored from shipping if "Same as Shipping" is selected.
- **Payment (`App\Models\Payment`):** Tracks transactions. For PayPal, stores both original LKR and settled USD amounts based on an exchange-rate snapshot.
- **Shipment (`App\Models\Shipment`):** Initial record is auto-created upon order placement (`SHP-YYYYMM-######`). Supports parcel metrics (weight, dimensions).
- **Invoice (`App\Models\Invoice`):** Auto-created record (`INV-YYYYMM-######`) for financial tracking.
- **FulfillmentStatusHistory (`App\Models\FulfillmentStatusHistory`):** Audit log of every status change across Order, Payment, and Shipment domains.

---

## State Management

Fulfillment is governed by `App\Services\Fulfillment\FulfillmentStatusService`, which enforces valid transitions and records history.

### Order Status (`App\Enums\OrderStatus`)
1. **Pending:** Order created, awaiting payment or verification.
2. **Paid:** Payment confirmed (Stripe/PayPal capture or admin-verified bank transfer).
3. **Confirmed:** Admin verified; ready for fulfillment.
4. **Fulfilled:** All shipments delivered.
5. **Closed:** Final state after return window/completion.
6. **Cancelled:** Order terminated.

### Payment Status (`App\Enums\PaymentStatus`)
- `pending`, `paid`, `failed`, `refunded`, `collection_pending` (for COD).

### Shipment Status (`App\Enums\ShipmentStatus`)
- `pending` -> `ready_for_packing` -> `packed` -> `ready_for_dispatch` -> `dispatched` -> `in_transit` -> `delivered`.
- **Automation:** When a shipment is marked `delivered`, the `Order` is automatically transitioned to `fulfilled`.

---

## Business Logic & Workflows

### 1. Pricing & Commission
- **Canonical Currency:** All pricing is handled in **LKR**.
- **Commission:** Fixed platform commission of **7.00%** is applied to each item.
- **Discounts:** Pricing is derived from a centralized logic considering both product-level and category-level discounts.

### 2. Checkout Workflow
- **Guest Checkout:** Allowed; captures `guest_name` and `guest_email`.
- **Address Selection:** Defaults to the country from the user's latest prior order.
- **PayPal Integration:** Shows `LKR -> USD` conversion summary using stored exchange-rate snapshots before payment. Supports both Wallet and Direct Card Fields.
- **Post-Placement:** Clear cart and redirect to `orders.confirmation` (guest access is session-backed).

### 3. Fulfillment & Reviews
- **Vendor Role:** Responsible for fulfillment when `shipping_responsibility = vendor`. Can update shipment status up to `ready_for_dispatch`.
- **Admin Role:** Full control over all transitions and tracking info assignment.
- **Product Reviews:** Authenticated customers can submit a review (1-5 stars) **only after** an order containing that product reaches `delivered` status.

---

## Technical Implementation Reference

### Controllers
- **Customer:** `App\Http\Controllers\OrderController`, `OrderConfirmationController`.
- **Admin:** `App\Http\Controllers\Admin\OrderController`.
- **Vendor:** `App\Http\Controllers\Vendor\OrderController`.
- **Checkout:** `CheckoutController`, `CheckoutPayPalController`, `CheckoutStripeController`.

### Key Services & Actions
- `App\Services\Fulfillment\FulfillmentStatusService` (State machine).
- `App\Actions\Order\BuildOrderProgress` (Progress UI data).
- `App\Services\Fulfillment\ShipmentLabelDataBuilder` (Label/Sticker data).

### Database Tables
- `orders`, `order_items`, `order_addresses`, `payments`, `shipments`, `invoices`, `fulfillment_status_histories`.

---

## Project Status

### Completed
- [x] Multi-method Checkout (Stripe, PayPal, Bank Transfer, COD).
- [x] LKR Pricing with 7% platform commission.
- [x] Automated Invoice and Shipment creation on placement.
- [x] State machine with History tracking.
- [x] Customer Order Detail & Dashboard History.
- [x] Post-delivery gated Reviews.
- [x] Admin & Vendor management interfaces.

### In Progress
- [ ] **Fulfillment Platform:** Refining logistics and dispatch workflows.

### Pending / Planned
- [ ] **Label Printing:** Support for thermal stickers (4x6) and PDF labels.
- [ ] **Mobile App Support:** APIs for mobile fulfillment app (scanning, real-time status updates).
- [ ] **Return Logistics:** Structured dispute and return-to-sender workflows.
- [ ] **Vendor Earnings:** Dedicated pages for vendor payouts and earnings tracking.

---

## Order Placement Technical Flow

Order creation is orchestrated by the `App\Actions\Order\PlaceOrder` action, which executes the following steps within a single database transaction:

1. **Authorization & Validation:**
   - Verifies user/guest access to the cart.
   - Ensures cart currency matches checkout currency (canonical **LKR**).
   - Validates that all products are `active` and their respective vendors are `approved`.

2. **Price & Commission Snapshots:**
   - Iterates through cart items to calculate `line_total` and `commission_amount` based on the configuration-defined rate.
   - Snapshots these values into `order_items` to protect against future price or rate changes.

3. **Payment Method Logic:**
   - **Instant (Stripe/PayPal):** Sets status to `paid`.
   - **COD:** Sets status to `collection_pending`.
   - **Bank Transfer:** Sets status to `pending`.
   - **PayPal Specifics:** If PayPal is used, the action verifies the `PayPalPaymentQuote` to ensure the final USD settlement matches the exchange-rate snapshot confirmed by the user.

4. **Entity Persistence:**
   - Creates the `Order` record.
   - Batch inserts `OrderAddress` records (Shipping & Billing).
   - Batch inserts `OrderItem` records.
   - Auto-creates an initial `Shipment`. If all items belong to a single vendor, the shipment is assigned to that `vendor_id`; otherwise, it remains unassigned (`null`) for platform aggregation.
   - Creates the `Payment` record with provider references.

5. **Cleanup:**
   - Clears cart items upon success.

---

## Testing Strategy

The system is verified through a multi-layered testing approach using **Pest PHP**:

- **Feature Tests:**
  - `OrderPlacementTest`: Verifies the end-to-end checkout flow for both guests and authenticated users.
  - `FulfillmentTransitionTest`: Tests the `FulfillmentStatusService` state machine, ensuring only valid transitions are allowed.
  - `PayPalCheckoutTest`: Specifically targets the LKR-USD conversion and quote verification logic.
- **Unit Tests:**
  - `OrderNumberGenerationTest`: Ensures the `ORD-YYYYMM-######` format is correctly assigned via model observers.
  - `CommissionCalculationTest`: Validates the math for platform fees across different quantities.
- **Access Control:**
  - `OrderPolicyTest`: Confirms that customers can only view their own orders, while vendors are restricted to items they fulfillment.

---

## Technical Notes
- **Database Indexing:** The `status` column on `orders`, `payments`, and `shipments` is indexed for performance.
- **Soft Deletes:** `Order` model uses `SoftDeletes` to prevent accidental data loss.
- **Public IDs:** Orders use high-entropy random strings for `public_id` to prevent ID enumeration.
- **Observers:** Model events (`creating`, `created`) are used for generating human-readable numbers and auto-invoicing, ensuring consistency regardless of the entry point (Web vs. API).

