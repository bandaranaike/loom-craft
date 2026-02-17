# LoomCraft — Implementation Status (Code-Verified)

Last reviewed: 2026-02-17
Scope: Verified against `routes/web.php`, `routes/settings.php`, `app/Http/Controllers`, `app/Actions`, `app/Services`, `resources/js/pages`, and `tests`.

Aligned with `.ai/architecture.md`, `.ai/implementation.md`, `.ai/best-practices.md`, `.ai/guardrails.md`, `.ai/dbschema.md`, and `.ai/order-process.md`.

## Current Delivery Snapshot

- Route surface: 37 web routes (`php artisan route:list --except-vendor`).
- Test inventory: 28 tests files total (27 feature, 1 unit).
- Service layer: one concrete external integration service at `app/Services/Video/YouTubeVideoUploader.php`.
- Frontend page surface: Inertia React pages are present for public, auth/settings, vendor, and admin areas under `resources/js/pages`.

## Implemented Features

### Authentication and Account Settings
- Fortify auth flow in place: login, register, password reset, email verification, password confirmation, and two-factor challenge.
- Settings implemented: profile edit/delete, password update, appearance page, and two-factor status page.
- Access control applied through route middleware and policy/gate checks.

### Public Storefront
- Home page (`/`) with:
  - latest approved active products,
  - approved community feedback highlights,
  - basic storefront metrics (`atelier_ledger`).
- Home page now includes authenticated feedback composer for vendor/customer users with edit-in-place behavior.
- Product index (`/products`) with search and pagination inputs.
- Product show (`/products/{product}`) restricted to active products from approved vendors.

### Cart and Checkout
- Guest and authenticated cart support with `loomcraft_guest_token` cookie for guest ownership.
- Cart item create/update/delete endpoints implemented.
- Checkout page enforces non-empty cart before rendering.
- Checkout store flow creates order aggregate and clears cart items.

### Orders
- Order placement persists to:
  - `orders`
  - `order_items`
  - `order_addresses`
  - `payments`
- Order confirmation route supports guest access through session-backed `guest_order_id` check.
- Customer order history (`/orders`) and order detail (`/orders/{order}`) implemented for authenticated users.
- Admin order list (`/admin/orders`) and vendor order items list (`/vendor/orders`) implemented.

### Vendor Features
- Vendor registration form and submission (`/vendor/register`).
- Vendor product index and create/store flow (`/vendor/products`, `/vendor/products/create`).
- Product creation enforces:
  - fixed commission `7.00`,
  - derived selling price (`vendor_price + 7%`),
  - image upload to `public` disk,
  - optional video upload through `VideoUploader` contract.
- Vendor feedback submission page and create action (`/vendor/feedback`) implemented.
- Feedback writes are upserted by `user_id` (single feedback record per authenticated user).

### Admin Features
- Pending vendor queue with search, pagination, per-page persistence, approve, and reject.
- YouTube OAuth connect/callback screens and callback handling for refresh token retrieval.
- Pending community feedback moderation list with approve action (vendor + buyer submissions).

## Implemented Pages (Inertia)

- Public: `welcome`, `products/index`, `products/show`, `cart`, `checkout`, `orders/confirmation`.
- Auth/settings: `auth/*`, `settings/profile`, `settings/password`, `settings/two-factor`, `settings/appearance`.
- Customer: `orders/index`, `orders/show`, `dashboard`.
- Vendor: `vendor/register`, `vendor/products/index`, `vendor/products/create`, `vendor/orders/index`, `vendor/feedback/create`.
- Admin: `admin/vendors/pending`, `admin/orders/index`, `admin/youtube/connect`, `admin/feedback/pending`.

## Test Coverage Present

- Auth + account settings (including two-factor challenge/settings and rate limiting).
- Public pages and dashboard access.
- Product index/show visibility rules.
- Vendor registration and vendor product workflows.
- Cart flow and checkout order creation.
- Customer order history.
- Admin vendor approvals, admin feedback approvals, and YouTube authorization callback.
- Database schema sanity checks.

## Partial / Gaps vs Architecture Scope

- Payment methods are captured as records/status only; no real Stripe charge lifecycle and no bank transfer admin verification workflow UI.
- Shipment, disputes, complaints, and product reports are modeled at DB level but not surfaced as completed user/admin workflows.
- Vendor shipping management and vendor payments/earnings pages are not implemented.
- Admin vendor CRUD beyond pending/approve/reject is not implemented.
- Product moderation workflow UI beyond vendor submission status is not implemented.
- Public informational pages are not implemented: About, Contact, Terms, Privacy, Cookie.
- Public vendor profile page is not implemented.

## Structural Notes

- Test folder has nested duplication patterns:
  - `tests/Feature/Feature/Admin/FeedbackApprovalTest.php`
  - `tests/Feature/Feature/VendorFeedbackTest.php`
  These tests run, but the directory shape is inconsistent with the rest of the suite.
