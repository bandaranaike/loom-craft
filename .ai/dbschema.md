# LoomCraft Database Schema (Authoritative Target)

This schema is the **authoritative target** aligned with the architecture and implementation guides. It focuses on core business logic and keeps framework/system tables out of scope except `users`, which is central to roles and permissions. Do not introduce new domain fields outside this file without updating it.

---

## Overview

Core business areas are covered:

- Accounts & roles
- Vendor onboarding & approval
- Products & media
- Cart & checkout (including guest checkout)
- Orders, shipping, payments, and payouts
- Disputes, complaints, and product reports
- Site improvement suggestions

---

## Tables

### users

Authentication + role holder.

- `id` (bigint, PK)
- `name` (varchar)
- `email` (varchar, unique)
- `email_verified_at` (timestamp, nullable)
- `password` (varchar)
- `role` (varchar) — `admin`, `vendor`, `customer`
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `users_email_unique` on `email`
- `users_role_index` on `role`

---

### vendors

Vendor profile and approval state.

- `id` (bigint, PK)
- `user_id` (bigint, FK → users.id)
- `display_name` (varchar)
- `bio` (text, nullable)
- `location` (varchar, nullable)
- `status` (varchar) — `pending`, `approved`, `rejected`, `suspended`
- `approved_at` (timestamp, nullable)
- `approved_by` (bigint, FK → users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `vendors_user_id_unique` on `user_id`
- `vendors_status_index` on `status`

---

### products

Primary product catalog record.

- `id` (bigint, PK)
- `vendor_id` (bigint, FK → vendors.id)
- `name` (varchar)
- `description` (text)
- `vendor_price` (decimal(10,2))
- `commission_rate` (decimal(5,2)) — default 7.00
- `selling_price` (decimal(10,2))
- `materials` (text, nullable)
- `pieces_count` (int, nullable)
- `production_time_days` (int, nullable)
- `dimension_length` (decimal(10,2), nullable)
- `dimension_width` (decimal(10,2), nullable)
- `dimension_height` (decimal(10,2), nullable)
- `dimension_unit` (varchar, nullable) — e.g. `cm`, `in`
- `status` (varchar) — `draft`, `pending_review`, `active`, `rejected`, `archived`
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `products_vendor_id_index` on `vendor_id`
- `products_status_index` on `status`

---

### product_media

Images and optional video.

- `id` (bigint, PK)
- `product_id` (bigint, FK → products.id)
- `type` (varchar) — `image`, `video`
- `path` (varchar)
- `alt_text` (varchar, nullable)
- `sort_order` (int, default 0)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `product_media_product_id_index` on `product_id`
- `product_media_type_index` on `type`

---

### carts

Guest carts allowed via `guest_token`.

- `id` (bigint, PK)
- `user_id` (bigint, FK → users.id, nullable)
- `guest_token` (varchar, nullable, unique)
- `currency` (varchar) — `USD`, `EUR`, `LKR`
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `carts_user_id_index` on `user_id`
- `carts_guest_token_unique` on `guest_token`

---

### cart_items

- `id` (bigint, PK)
- `cart_id` (bigint, FK → carts.id)
- `product_id` (bigint, FK → products.id)
- `quantity` (int)
- `unit_price` (decimal(10,2))
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `cart_items_cart_id_index` on `cart_id`
- `cart_items_product_id_index` on `product_id`

---

### orders

Supports guest checkout via nullable `user_id` and guest fields.

- `id` (bigint, PK)
- `user_id` (bigint, FK → users.id, nullable)
- `guest_name` (varchar, nullable)
- `guest_email` (varchar, nullable)
- `status` (varchar) — `pending`, `paid`, `processing`, `shipped`, `delivered`, `cancelled`, `disputed`
- `currency` (varchar) — `USD`, `EUR`, `LKR`
- `subtotal` (decimal(10,2))
- `commission_total` (decimal(10,2))
- `total` (decimal(10,2))
- `shipping_responsibility` (varchar) — `vendor`, `platform`
- `placed_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `orders_user_id_index` on `user_id`
- `orders_status_index` on `status`

---

### order_items

- `id` (bigint, PK)
- `order_id` (bigint, FK → orders.id)
- `product_id` (bigint, FK → products.id)
- `vendor_id` (bigint, FK → vendors.id)
- `quantity` (int)
- `unit_price` (decimal(10,2))
- `commission_rate` (decimal(5,2))
- `commission_amount` (decimal(10,2))
- `line_total` (decimal(10,2))
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `order_items_order_id_index` on `order_id`
- `order_items_vendor_id_index` on `vendor_id`

---

### order_addresses

Separate billing/shipping for compliance and guest checkout.

- `id` (bigint, PK)
- `order_id` (bigint, FK → orders.id)
- `type` (varchar) — `billing`, `shipping`
- `full_name` (varchar)
- `line1` (varchar)
- `line2` (varchar, nullable)
- `city` (varchar)
- `region` (varchar, nullable)
- `postal_code` (varchar, nullable)
- `country_code` (varchar)
- `phone` (varchar, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `order_addresses_order_id_index` on `order_id`
- `order_addresses_type_index` on `type`

---

### shipments

Tracks shipping responsibility and fulfillment.

- `id` (bigint, PK)
- `order_id` (bigint, FK → orders.id)
- `vendor_id` (bigint, FK → vendors.id, nullable)
- `responsibility` (varchar) — `vendor`, `platform`
- `status` (varchar) — `pending`, `in_transit`, `delivered`, `returned`
- `carrier` (varchar, nullable)
- `tracking_number` (varchar, nullable)
- `shipped_at` (timestamp, nullable)
- `delivered_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `shipments_order_id_index` on `order_id`
- `shipments_vendor_id_index` on `vendor_id`

---

### payments

Single source for checkout payments.

- `id` (bigint, PK)
- `order_id` (bigint, FK → orders.id)
- `method` (varchar) — `stripe`, `bank_transfer`, `cod`
- `status` (varchar) — `pending`, `authorized`, `paid`, `failed`, `refunded`
- `amount` (decimal(10,2))
- `currency` (varchar) — `USD`, `EUR`, `LKR`
- `provider_reference` (varchar, nullable)
- `verified_by` (bigint, FK → users.id, nullable)
- `verified_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `payments_order_id_index` on `order_id`
- `payments_status_index` on `status`

---

### vendor_payouts

Earnings distribution to vendors.

- `id` (bigint, PK)
- `vendor_id` (bigint, FK → vendors.id)
- `order_id` (bigint, FK → orders.id, nullable)
- `amount` (decimal(10,2))
- `currency` (varchar) — `USD`, `EUR`, `LKR`
- `status` (varchar) — `pending`, `paid`, `failed`
- `paid_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `vendor_payouts_vendor_id_index` on `vendor_id`

---

### disputes

Manual refund flow and dispute handling.

- `id` (bigint, PK)
- `order_id` (bigint, FK → orders.id)
- `order_item_id` (bigint, FK → order_items.id, nullable)
- `opened_by_user_id` (bigint, FK → users.id, nullable)
- `status` (varchar) — `open`, `under_review`, `resolved`, `rejected`
- `reason` (text)
- `resolution` (text, nullable)
- `refund_amount` (decimal(10,2), nullable)
- `handled_by` (bigint, FK → users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `disputes_order_id_index` on `order_id`
- `disputes_status_index` on `status`

---

### complaints

General complaints are not limited to a single product.

- `id` (bigint, PK)
- `user_id` (bigint, FK → users.id, nullable)
- `guest_email` (varchar, nullable)
- `subject` (varchar)
- `message` (text)
- `status` (varchar) — `open`, `under_review`, `resolved`, `rejected`
- `handled_by` (bigint, FK → users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `complaints_status_index` on `status`

---

### product_reports

Reports for product issues or concerns.

- `id` (bigint, PK)
- `product_id` (bigint, FK → products.id)
- `user_id` (bigint, FK → users.id, nullable)
- `guest_email` (varchar, nullable)
- `reason` (text)
- `status` (varchar) — `open`, `under_review`, `resolved`, `rejected`
- `handled_by` (bigint, FK → users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `product_reports_product_id_index` on `product_id`
- `product_reports_status_index` on `status`

---

### suggestions

Site improvement suggestions (admin review).

- `id` (bigint, PK)
- `user_id` (bigint, FK → users.id, nullable)
- `guest_email` (varchar, nullable)
- `title` (varchar)
- `details` (text)
- `status` (varchar) — `open`, `under_review`, `resolved`, `rejected`
- `handled_by` (bigint, FK → users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `suggestions_status_index` on `status`

---

## Relationships Summary

- `users` 1—1 `vendors`
- `vendors` 1—* `products`
- `products` 1—* `product_media`
- `carts` 1—* `cart_items`
- `orders` 1—* `order_items`
- `orders` 1—* `order_addresses`
- `orders` 1—* `shipments`
- `orders` 1—1 `payments`
- `vendors` 1—* `vendor_payouts`
- `orders` 1—* `disputes`
- `products` 1—* `product_reports`

---

## Notes / Clarifications Needed

- If you want multi‑currency pricing per product (beyond order currency), confirm a pricing strategy.
- If you want inventory/stock tracking, confirm fields and workflow.
- If you want vendor‑specific shipping rates or carriers, confirm rules.
