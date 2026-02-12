# LoomCraft — Order Process

This document defines the end-to-end ordering workflow for LoomCraft, aligned with the approved architecture, guardrails, and proposed database schema. It describes how carts, checkout, payments, orders, and post‑order actions work for guests and authenticated users.

---

## Alignment (Keep In Sync)

- `.ai/guardrails.md` (hard non-negotiables)
- `.ai/best-practices.md` (implementation rules)
- `.ai/dbschema.md` (authoritative domain fields)
- `.ai/implementation.md` (implementation guidance)

---

## 1) Core Principles (Must Enforce)

- Multi‑vendor marketplace with a fixed **7%** platform commission on every item.
- Guest checkout is allowed.
- Supported currencies: **USD**, **EUR**, **LKR**.
- Payment methods: **Stripe**, **Manual bank transfer (admin‑verified)**, **Cash on delivery**.
- Refunds are **manual only** via disputes (no automated refunds).
- Shipping responsibility per order: **vendor** or **platform**.
- All controllers return **Inertia** responses.

---

## 2) Primary Data Touchpoints (Schema‑Aligned)

- **Cart**: `carts`, `cart_items`
- **Order**: `orders`, `order_items`, `order_addresses`
- **Product**: `products`, `product_media`
- **Vendor**: `vendors`
- **User**: `users`

No additional fields are introduced beyond `.ai/dbschema.md`.

---

## 3) Cart Lifecycle

### 3.1 Create / Fetch Cart

**Guest**
- A cart is created with a unique `guest_token`.
- Cart persists via browser storage / cookie token.

**Authenticated user**
- Cart is created or retrieved by `user_id`.
- If a guest cart exists, it may be associated to the user on login.

### 3.2 Add to Cart

- Validate product status is purchasable (e.g., `active`).
- Add or increment `cart_items`:
  - `product_id`
  - `quantity`
  - `unit_price` = product `selling_price`
- Unit price is **always derived** from product selling price; never user‑provided.

### 3.3 Update Cart

- Users can update quantities or remove items.
- If quantity becomes 0, remove the item.
- Update cart totals for display only (totals are recalculated at checkout).

### 3.4 Cart Validation Rules

- Ensure currency consistency across items in a single cart.
- Re‑validate pricing and availability during checkout.

---

## 4) Checkout Flow

### 4.1 Checkout Entry

- Cart must have at least one valid item.
- Checkout allows:
  - **Guest checkout** (name + email captured into order).
  - **Authenticated checkout** (user_id attached to order).

### 4.2 Address Collection

- Collect **shipping** and **billing** address details.
- Persist into `order_addresses` with `type = shipping|billing`.

### 4.3 Pricing & Commission

- For each item:
  - `unit_price` = product `selling_price`
  - `commission_rate` = **7.00**
  - `commission_amount` calculated per line
  - `line_total` = `unit_price * quantity`
- Order totals:
  - `subtotal` = sum of `line_total`
  - `commission_total` = sum of `commission_amount`
  - `total` = `subtotal` (plus shipping if applicable; handled externally)

### 4.4 Shipping Responsibility

- Set `orders.shipping_responsibility` to **vendor** or **platform**.
- This choice is stored per order and applied in vendor/admin workflows.

### 4.5 Payment Selection

- User chooses one method: **Stripe**, **Bank Transfer**, **Cash on Delivery**.
- Stripe proceeds to payment capture.
- Bank transfer is **pending** until admin verification.
- COD is marked as pending/processing until fulfillment.

---

## 5) Order Creation

### 5.1 Create Order Record

- Create `orders` with:
  - `user_id` or `guest_name` / `guest_email`
  - `status` (initial: `pending` or `paid` depending on method)
  - `currency`
  - `subtotal`, `commission_total`, `total`
  - `shipping_responsibility`
  - `placed_at`

### 5.2 Create Order Items

For each cart item:
- Create `order_items` with:
  - `order_id`
  - `product_id`
  - `vendor_id`
  - `quantity`
  - `unit_price`
  - `commission_rate` (7.00)
  - `commission_amount`
  - `line_total`

### 5.3 Create Order Addresses

- Store `billing` and `shipping` as `order_addresses`.

### 5.4 Cart Finalization

- Clear cart items after successful order creation.
- Preserve cart record for future reuse if needed.

---

## 6) Order Status Lifecycle

Typical status progression (may vary by payment method):

1. `pending` → order created, awaiting payment or verification
2. `paid` → payment confirmed (Stripe or admin-verified bank transfer)
3. `processing` → vendor / platform preparing shipment
4. `shipped` → dispatch confirmed
5. `delivered` → completed fulfillment
6. `cancelled` → cancelled by admin or system policy
7. `disputed` → escalated into dispute flow

---

## 7) Order Confirmation

- Show order confirmation page with:
  - Order summary
  - Items and totals
  - Shipping responsibility
  - Payment method status
- For guest checkout, provide a reference method to access order (email receipt).

---

## 8) Order History & Tracking

### 8.1 Customer Order History

- Authenticated users can see all orders by `user_id`.
- Display status, totals, and line items.

### 8.2 Guest Order Lookup

- Guest receives confirmation email to view order.
- Access is scoped to the provided order reference.

### 8.3 Vendor Order View

- Vendors see `order_items` filtered by their `vendor_id`.
- Vendor is responsible for fulfillment when `shipping_responsibility = vendor`.

### 8.4 Admin Order View

- Admin can view all orders, statuses, and disputes.

---

## 9) Disputes, Complaints, and Reports

- Disputes can be raised on orders or items (no automated refunds).
- Admin handles resolution and manual refunds.
- Complaints and product reports follow admin moderation flow.

---

## 10) Inertia Page Coverage (Ordering)

Public
- Cart
- Checkout
- Order confirmation

Authenticated
- Order history
- Order detail

Vendor
- Vendor order management

Admin
- Order management + dispute handling

---

## 11) Guardrails & Non‑Negotiables

- Selling price **never** accepted from user input; derived from vendor price + 7%.
- Refunds **manual only**.
- No changes to payment methods or currency list without approval.
- All permissions enforced server‑side.
