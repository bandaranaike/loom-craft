# LoomCraft — Implementation Status (Code-Verified)

Last reviewed: 2026-02-12
Scope: Verified against `routes/`, `app/`, `resources/js/`, and `database/migrations`.

Aligned with `.ai/architecture.md`, `.ai/implementation.md`, `.ai/best-practices.md`, `.ai/guardrails.md`,
`.ai/dbschema.md`, and `.ai/order-process.md`.

## Completed Features

### Authentication & Account Settings
- Fortify-based auth flow: login, registration, password reset, email verification, and two-factor challenge.
- Account settings: profile update/delete, password update, two-factor setup, and appearance settings.
- Auth scaffolding pages exist under `resources/js/pages/auth/*` and `resources/js/pages/settings/*`.

### Public Storefront
- Home page (`/`) and dashboard placeholder (`/dashboard`).
- Product listing (`/products`) with search/per-page inputs.
- Product detail page (`/products/{product}`).

### Cart & Checkout
- Cart view with guest cart support (guest token cookie).
- Add/update/remove cart items.
- Checkout view with currency, shipping responsibility, and payment method selection.
- Guest checkout supported (guest name/email captured).

### Orders
- Order placement creates:
  - `orders`, `order_items`, `order_addresses`, `payments` records.
- Order confirmation page (`/orders/{order}/confirmation`).
- Customer order history and order detail (`/orders`, `/orders/{order}`).

### Vendor Experience
- Vendor registration flow (`/vendor/register`).
- Vendor product list + create flow (`/vendor/products`, `/vendor/products/create`).
- Vendor order items list (`/vendor/orders`).

### Admin Experience
- Pending vendor approvals list + approve/reject actions.
- Admin order list (`/admin/orders`).
- YouTube OAuth connect + callback for video upload authorization.

### Product Media Handling
- Image uploads stored in local storage (`public` disk).
- Video uploads via YouTube API uploader service (stores YouTube URL).

### Core Domain Models & Policies
- Models: `User`, `Vendor`, `Product`, `ProductMedia`, `Cart`, `CartItem`, `Order`, `OrderItem`, `OrderAddress`, `Payment`, `Shipment`, `VendorPayout`, `Dispute`, `Complaint`, `ProductReport`, `Suggestion`.
- Policies present for Admin, Vendor, Product, Cart, and Order.
- Value objects for money, currency, and dimensions.

## Partial / Notes
- Payment processing is recorded (Stripe/bank transfer/COD) but no provider integration logic exists beyond status assignment.
- Order shipping lifecycle (shipment tracking updates) is modeled but no UI/workflows are implemented.

## Not Yet Implemented (From Architecture/Requirements)
- Admin vendor CRUD beyond approve/reject workflows.
- Product moderation workflows and related admin UI.
- Complaints, product reports, disputes, and suggestions UI/workflows.
- Vendor payments/earnings views and vendor shipping management UI.
- Public content pages: About, Contact, Terms, Privacy, Cookie policy.
- Vendor profile public page.

## Implemented Pages (Inertia)
- Public: `welcome`, `products/index`, `products/show`, `cart`, `checkout`, `orders/confirmation`.
- Auth: `auth/*` pages, `settings/*` pages.
- Vendor: `vendor/register`, `vendor/products/index`, `vendor/products/create`, `vendor/orders/index`.
- Admin: `admin/vendors/pending`, `admin/orders/index`, `admin/youtube/connect`.

## Tests Present (Coverage of Implemented Areas)
- Feature tests for auth, cart flow, checkout, orders, vendor registration, product creation, vendor approvals, YouTube authorization, and schema checks.
