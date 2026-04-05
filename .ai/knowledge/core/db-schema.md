# LoomCraft Database Schema

Schema snapshot derived from `.ai/db.sql`, the latest SQL dump of the database.

Last synchronized with `.ai/db.sql`: 2026-04-05.

---

## Overview

This document reflects the current database schema for LoomCraft's business domain. It focuses on domain and application tables and intentionally omits framework/runtime tables except where they materially affect product behavior.

Included business areas:

- Accounts, roles, and billing metadata
- Vendor profiles, locations, and contact submissions
- Products, media, categories, colors, reviews, and reports
- Cart, checkout, orders, shipping, payments, disputes, and payouts
- Exchange rates
- Complaints and site suggestions

Omitted framework/runtime tables:

- `cache`
- `cache_locks`
- `failed_jobs`
- `job_batches`
- `jobs`
- `migrations`
- `password_reset_tokens`
- `sessions`

Cashier tables included because they are part of the application's billing model:

- `subscriptions`
- `subscription_items`

---

## Tables

### users

Authentication, roles, and Cashier billing metadata.

- `id` (bigint unsigned, PK)
- `name` (varchar(255))
- `email` (varchar(255), unique)
- `email_verified_at` (timestamp, nullable)
- `password` (varchar(255))
- `role` (varchar(255), default `customer`)
- `two_factor_secret` (text, nullable)
- `two_factor_recovery_codes` (text, nullable)
- `two_factor_confirmed_at` (timestamp, nullable)
- `remember_token` (varchar(100), nullable)
- `stripe_id` (varchar(255), nullable)
- `pm_type` (varchar(255), nullable)
- `pm_last_four` (varchar(4), nullable)
- `trial_ends_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `users_email_unique` on `email`
- `users_role_index` on `role`
- `users_stripe_id_index` on `stripe_id`

---

### vendors

Vendor profile, public storefront profile data, and approval state.

- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id)
- `display_name` (varchar(255))
- `slug` (varchar(255), nullable, unique)
- `bio` (text, nullable)
- `tagline` (varchar(255), nullable)
- `website_url` (varchar(255), nullable)
- `contact_email` (varchar(255), nullable)
- `contact_phone` (varchar(50), nullable)
- `whatsapp_number` (varchar(50), nullable)
- `logo_path` (varchar(255), nullable)
- `cover_image_path` (varchar(255), nullable)
- `about_title` (varchar(255), nullable)
- `craft_specialties` (JSON stored in longtext, nullable)
- `years_active` (smallint unsigned, nullable)
- `is_contact_public` (boolean/tinyint(1), default `1`)
- `is_website_public` (boolean/tinyint(1), default `1`)
- `location` (varchar(255), nullable)
- `status` (varchar(255))
- `approved_at` (timestamp, nullable)
- `approved_by` (bigint unsigned, FK -> users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `vendors_slug_unique` on `slug`
- `vendors_status_index` on `status`
- `vendors_status_display_name_index` on (`status`, `display_name`)

---

### vendor_locations

Vendor physical/store/contact locations.

- `id` (bigint unsigned, PK)
- `vendor_id` (bigint unsigned, FK -> vendors.id)
- `location_name` (varchar(255))
- `address_line_1` (varchar(255))
- `address_line_2` (varchar(255), nullable)
- `city` (varchar(255))
- `region` (varchar(255), nullable)
- `postal_code` (varchar(255), nullable)
- `country` (varchar(255))
- `phone` (varchar(50), nullable)
- `hours` (varchar(255), nullable)
- `map_url` (varchar(255), nullable)
- `is_primary` (boolean/tinyint(1), default `0`)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `vendor_locations_vendor_id_index` on `vendor_id`

---

### vendor_contact_submissions

Inbound public contact submissions for vendors.

- `id` (bigint unsigned, PK)
- `vendor_id` (bigint unsigned, FK -> vendors.id)
- `name` (varchar(255))
- `email` (varchar(255))
- `phone` (varchar(50), nullable)
- `subject` (varchar(255))
- `message` (text)
- `status` (varchar(255), default `pending`)
- `handled_by` (bigint unsigned, FK -> users.id, nullable)
- `handled_at` (timestamp, nullable)
- `submitted_at` (timestamp, default current timestamp)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `vendor_contact_submissions_vendor_id_status_index` on (`vendor_id`, `status`)

---

### product_categories

Product taxonomy with optional category-level discount metadata.

- `id` (bigint unsigned, PK)
- `name` (varchar(255), unique)
- `slug` (varchar(255), unique)
- `description` (text, nullable)
- `discount_percentage` (decimal(5,2), nullable)
- `is_active` (boolean/tinyint(1), default `1`)
- `sort_order` (int unsigned, default `0`)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `product_categories_name_unique` on `name`
- `product_categories_slug_unique` on `slug`

---

### product_colors

Reusable product color taxonomy.

- `id` (bigint unsigned, PK)
- `name` (varchar(255), unique)
- `slug` (varchar(255), unique)
- `is_active` (boolean/tinyint(1), default `1`)
- `sort_order` (int unsigned, default `0`)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `product_colors_name_unique` on `name`
- `product_colors_slug_unique` on `slug`

---

### products

Primary product catalog record.

- `id` (bigint unsigned, PK)
- `vendor_id` (bigint unsigned, FK -> vendors.id)
- `name` (varchar(255))
- `product_code` (varchar(100), unique)
- `slug` (varchar(255), unique)
- `description` (text)
- `vendor_price` (decimal(10,2))
- `commission_rate` (decimal(5,2), default `7.00`)
- `selling_price` (decimal(10,2))
- `discount_percentage` (decimal(5,2), nullable)
- `materials` (text, nullable)
- `pieces_count` (int unsigned, nullable)
- `production_time_days` (int unsigned, nullable)
- `dimension_length` (decimal(10,2), nullable)
- `dimension_width` (decimal(10,2), nullable)
- `dimension_height` (decimal(10,2), nullable)
- `dimension_unit` (varchar(255), nullable)
- `status` (varchar(255))
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `products_product_code_unique` on `product_code`
- `products_slug_unique` on `slug`
- `products_status_index` on `status`
- `products_status_created_at_index` on (`status`, `created_at`)
- `products_status_selling_price_index` on (`status`, `selling_price`)

Important note:
- The SQL dump currently defines a DB default of `7.00` for `commission_rate`. Project notes indicate create/edit flows and commission calculations use `COMMERCE_COMMISSION_RATE` from config/environment, with a current default of `100.00`. This is an application/schema mismatch that should be treated carefully.

---

### category_product

Many-to-many pivot between products and categories.

- `id` (bigint unsigned, PK)
- `product_id` (bigint unsigned, FK -> products.id)
- `product_category_id` (bigint unsigned, FK -> product_categories.id)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `category_product_product_id_product_category_id_unique` on (`product_id`, `product_category_id`)

---

### product_color_product

Many-to-many pivot between products and colors.

- `id` (bigint unsigned, PK)
- `product_id` (bigint unsigned, FK -> products.id)
- `product_color_id` (bigint unsigned, FK -> product_colors.id)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `product_color_product_product_id_product_color_id_unique` on (`product_id`, `product_color_id`)

---

### product_media

Images and optional video for products.

- `id` (bigint unsigned, PK)
- `product_id` (bigint unsigned, FK -> products.id)
- `type` (varchar(255))
- `path` (varchar(255))
- `alt_text` (varchar(255), nullable)
- `sort_order` (int unsigned, default `0`)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `product_media_type_index` on `type`

---

### product_reviews

Delivered-purchase customer reviews shown on public product pages.

Status:
- Referenced by project knowledge and completed work, but not present in the current `.ai/db.sql` dump.

Implication:
- If reviews are still a live feature, the SQL dump is missing that table.
- If the dump is authoritative, review-related knowledge and implementation docs should be updated separately.

---

### carts

Shopping carts for authenticated or guest users.

- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id, nullable)
- `guest_token` (varchar(255), nullable, unique)
- `currency` (varchar(255))
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `carts_guest_token_unique` on `guest_token`

---

### cart_items

- `id` (bigint unsigned, PK)
- `cart_id` (bigint unsigned, FK -> carts.id)
- `product_id` (bigint unsigned, FK -> products.id)
- `quantity` (int unsigned)
- `unit_price` (decimal(10,2))
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

No explicit standalone indexes beyond foreign-key support.

---

### orders

Supports guest checkout via nullable `user_id` and guest fields.

- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id, nullable)
- `guest_name` (varchar(255), nullable)
- `guest_email` (varchar(255), nullable)
- `status` (varchar(255))
- `currency` (varchar(255))
- `subtotal` (decimal(10,2))
- `commission_total` (decimal(10,2))
- `total` (decimal(10,2))
- `shipping_responsibility` (varchar(255))
- `placed_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `orders_status_index` on `status`

---

### order_items

- `id` (bigint unsigned, PK)
- `order_id` (bigint unsigned, FK -> orders.id)
- `product_id` (bigint unsigned, FK -> products.id)
- `vendor_id` (bigint unsigned, FK -> vendors.id)
- `quantity` (int unsigned)
- `unit_price` (decimal(10,2))
- `commission_rate` (decimal(5,2))
- `commission_amount` (decimal(10,2))
- `line_total` (decimal(10,2))
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `order_items_vendor_id_index` on `vendor_id`

---

### order_addresses

Separate billing/shipping addresses per order.

- `id` (bigint unsigned, PK)
- `order_id` (bigint unsigned, FK -> orders.id)
- `type` (varchar(255))
- `full_name` (varchar(255))
- `line1` (varchar(255))
- `line2` (varchar(255), nullable)
- `city` (varchar(255))
- `region` (varchar(255), nullable)
- `postal_code` (varchar(255), nullable)
- `country_code` (varchar(255))
- `phone` (varchar(255), nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `order_addresses_type_index` on `type`

---

### shipments

Tracks shipping responsibility and fulfillment.

- `id` (bigint unsigned, PK)
- `order_id` (bigint unsigned, FK -> orders.id)
- `vendor_id` (bigint unsigned, FK -> vendors.id, nullable)
- `responsibility` (varchar(255))
- `status` (varchar(255))
- `carrier` (varchar(255), nullable)
- `tracking_number` (varchar(255), nullable)
- `shipped_at` (timestamp, nullable)
- `delivered_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

No explicit standalone indexes defined in the dump.

---

### payments

Payment records for checkout and verification.

- `id` (bigint unsigned, PK)
- `order_id` (bigint unsigned, FK -> orders.id)
- `method` (varchar(255))
- `status` (varchar(255))
- `amount` (decimal(10,2))
- `currency` (varchar(255))
- `original_amount` (decimal(10,2), nullable)
- `original_currency` (varchar(3), nullable)
- `exchange_rate` (decimal(18,8), nullable)
- `exchange_rate_source` (varchar(255), nullable)
- `exchange_rate_fetched_at` (timestamp, nullable)
- `provider_reference` (varchar(255), nullable)
- `verified_by` (bigint unsigned, FK -> users.id, nullable)
- `verified_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `payments_status_index` on `status`

Implementation note:
- `order_id` is not unique at the DB level.

---

### exchange_rates

Historical exchange-rate cache for currency conversion.

- `id` (bigint unsigned, PK)
- `from_currency` (varchar(3))
- `to_currency` (varchar(3))
- `rate` (decimal(18,8))
- `source` (varchar(255))
- `fetched_at` (timestamp)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `exchange_rates_pair_fetched_at_index` on (`from_currency`, `to_currency`, `fetched_at`)

---

### vendor_payouts

Earnings distribution to vendors.

- `id` (bigint unsigned, PK)
- `vendor_id` (bigint unsigned, FK -> vendors.id)
- `order_id` (bigint unsigned, FK -> orders.id, nullable)
- `amount` (decimal(10,2))
- `currency` (varchar(255))
- `status` (varchar(255))
- `paid_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

No explicit standalone indexes defined in the dump.

---

### disputes

Manual refund flow and dispute handling.

- `id` (bigint unsigned, PK)
- `order_id` (bigint unsigned, FK -> orders.id)
- `order_item_id` (bigint unsigned, FK -> order_items.id, nullable)
- `opened_by_user_id` (bigint unsigned, FK -> users.id, nullable)
- `status` (varchar(255))
- `reason` (text)
- `resolution` (text, nullable)
- `refund_amount` (decimal(10,2), nullable)
- `handled_by` (bigint unsigned, FK -> users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `disputes_status_index` on `status`

---

### complaints

General complaints not limited to a single product.

- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id, nullable)
- `guest_email` (varchar(255), nullable)
- `subject` (varchar(255))
- `message` (text)
- `status` (varchar(255))
- `handled_by` (bigint unsigned, FK -> users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `complaints_status_index` on `status`

---

### product_reports

Reports for product issues or concerns.

- `id` (bigint unsigned, PK)
- `product_id` (bigint unsigned, FK -> products.id)
- `user_id` (bigint unsigned, FK -> users.id, nullable)
- `guest_email` (varchar(255), nullable)
- `reason` (text)
- `status` (varchar(255))
- `handled_by` (bigint unsigned, FK -> users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `product_reports_status_index` on `status`

---

### suggestions

Site improvement suggestions.

- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id, nullable)
- `guest_email` (varchar(255), nullable)
- `title` (varchar(255))
- `details` (text)
- `status` (varchar(255))
- `handled_by` (bigint unsigned, FK -> users.id, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `suggestions_status_index` on `status`

---

### subscriptions

Laravel Cashier subscriptions.

- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id)
- `type` (varchar(255))
- `stripe_id` (varchar(255), unique)
- `stripe_status` (varchar(255))
- `stripe_price` (varchar(255), nullable)
- `quantity` (int, nullable)
- `trial_ends_at` (timestamp, nullable)
- `ends_at` (timestamp, nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `subscriptions_stripe_id_unique` on `stripe_id`
- `subscriptions_user_id_stripe_status_index` on (`user_id`, `stripe_status`)

---

### subscription_items

Laravel Cashier subscription line items.

- `id` (bigint unsigned, PK)
- `subscription_id` (bigint unsigned, FK -> subscriptions.id)
- `stripe_id` (varchar(255), unique)
- `stripe_product` (varchar(255))
- `stripe_price` (varchar(255))
- `meter_id` (varchar(255), nullable)
- `quantity` (int, nullable)
- `meter_event_name` (varchar(255), nullable)
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

Indexes:
- `subscription_items_stripe_id_unique` on `stripe_id`
- `subscription_items_subscription_id_stripe_price_index` on (`subscription_id`, `stripe_price`)

---

## Relationships Summary

- `users` 1-1 `vendors`
- `vendors` 1-* `vendor_locations`
- `vendors` 1-* `vendor_contact_submissions`
- `vendors` 1-* `products`
- `products` *-* `product_categories` via `category_product`
- `products` *-* `product_colors` via `product_color_product`
- `products` 1-* `product_media`
- `products` 1-* `product_reports`
- `carts` 1-* `cart_items`
- `orders` 1-* `order_items`
- `orders` 1-* `order_addresses`
- `orders` 1-* `shipments`
- `orders` 1-* `payments`
- `vendors` 1-* `vendor_payouts`
- `orders` 1-* `disputes`
- `users` 1-* `subscriptions`
- `subscriptions` 1-* `subscription_items`

---

## Notes

- `product_reviews` is not present in the SQL dump even though existing knowledge/tasks indicate reviews were implemented.
- Several tables rely only on foreign-key support indexes and do not have separately named explicit indexes in the dump.
- The database currently includes exchange-rate fields on `payments` plus a dedicated `exchange_rates` table, which means currency conversion is now part of persisted checkout/payment data.
