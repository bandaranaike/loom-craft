# LoomCraft — Implementation Status (Code-Verified)

Last reviewed: 2026-03-07
Scope: Verified against `routes/web.php`, `routes/settings.php`, `app/Http/Controllers`, `app/Actions`, `app/Services`, `resources/js/pages`, and `tests`.

Aligned with `.ai/knowledge/core/architecture.md`, `.ai/knowledge/core/implementation-guide.md`, `.ai/knowledge/core/best-practices.md`, `.ai/knowledge/core/guardrails.md`, `.ai/knowledge/core/db-schema.md`, and `.ai/knowledge/core/order-process.md`.

## Current Delivery Snapshot

- Route surface: 38 web routes (`php artisan route:list --except-vendor`).
- Test inventory: 29 tests files total (28 feature, 1 unit).
- Service layer: one concrete external integration service at `app/Services/Video/YouTubeVideoUploader.php`.
- Frontend page surface: Inertia React pages are present for public, auth/settings, vendor, and admin areas under `resources/js/pages`.

## Implemented Features

### Authentication and Account Settings
- Fortify auth flow in place: login, register, password reset, email verification, password confirmation, and two-factor challenge.
- Settings implemented: profile edit/delete, password update, appearance page, and two-factor status page.
- Access control applied through route middleware and policy/gate checks.

### Public Storefront
- Home page (`/`) with:
  - random approved active products,
  - a full-width hero panel without the previous `Atelier Ledger`, `Atelier Voices`, or `Craftsmanship Flow` sections.
- Build Your Own Woven page (`/loom-weave-demo`) with interactive grid painting, compile preview, undo/redo history, constraint visibility, and PNG export.
- Product index (`/products`) with search and pagination inputs.
- Product show (`/products/{product}`) restricted to active products from approved vendors.
- Public vendor storefront page (`/vendors/{slug}`) implemented with vendor details, filtered products, category summaries, location blocks, and inquiry form handling.

### Cart and Checkout
- Guest and authenticated cart support with `loomcraft_guest_token` cookie for guest ownership.
- Default cart/session currency is now `LKR` when not explicitly provided.
- Cart item create/update/delete endpoints implemented.
- Cart page now includes a continue-shopping CTA for customers who want to add more products before checkout.
- Checkout page enforces non-empty cart before rendering.
- Checkout store flow creates order aggregate and clears cart items.
- Checkout country selection now uses explicit country selectors instead of a hardcoded US default, with authenticated users defaulting from their latest prior order country when available and otherwise falling back to the configured application default.
- Checkout keeps billing-details mirroring available and hides the billing form when billing matches shipping.
- Checkout now shows PayPal LKR to USD conversion details before payment starts.
- PayPal checkout supports both wallet redirect approval and on-page direct card entry via PayPal Card Fields.
- Exchange-rate snapshots are stored historically and used to block stale PayPal conversions.
- Exchange-rate syncing is implemented through a dedicated command and exchange-rate storage.
- Cart, checkout, and order placement use centralized discounted pricing derived from product and category discounts.

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
- Vendor product edit/update flow is implemented (`/vendor/products/{product}/edit`, `/vendor/products/{product}`).
- Vendor product image management is implemented from edit page (upload + delete via `/vendor/products/{product}/images` and `/vendor/products/{product}/images/{image}`).
- Product creation enforces:
  - fixed commission `7.00`,
  - derived selling price (`vendor_price + 7%`),
  - image upload to `public` disk,
  - optional video upload through `VideoUploader` contract.
- Vendor feedback submission page and create action (`/vendor/feedback`) implemented.
- Feedback writes are upserted by `user_id` (single feedback record per authenticated user).
- Public product pages resolve by slug at `/product/{slug}` with automatically generated unique product slugs.
- Vendor public storefront profile edit flow is implemented and exposes the broader public-facing vendor dataset, including visibility flags and locations.

### Admin Features
- Pending vendor queue with search, pagination, per-page persistence, approve, and reject.
- YouTube OAuth connect/callback screens and callback handling for refresh token retrieval.
- Pending community feedback moderation list with approve action (vendor + buyer submissions).

## Implemented Pages (Inertia)

- Public: `welcome`, `loom-weave-demo`, `products/index`, `products/show`, `cart`, `checkout`, `orders/confirmation`.
- Public legal: `privacy-policy`, `terms-of-service`.
- Auth/settings: `auth/*`, `settings/profile`, `settings/password`, `settings/two-factor`, `settings/appearance`.
- Customer: `orders/index`, `orders/show`, `dashboard`.
- Vendor: `vendor/register`, `vendor/products/index`, `vendor/products/create`, `vendor/orders/index`, `vendor/feedback/create`.
- Admin: `admin/vendors/pending`, `admin/orders/index`, `admin/youtube/connect`, `admin/feedback/pending`.

## Test Coverage Present

- Auth + account settings (including two-factor challenge/settings and rate limiting).
- Public pages and dashboard access.
- Public loom weave demo route/component availability.
- Product detail gallery navigation, swipe support, and image-anchored floating thumbnail behavior when the gallery reaches the viewport edge.
- Product catalog filters include swatch-based color selection using the shared product color palette.
- GitHub Actions workflows now install frontend dependencies and build assets with pnpm to match the repository package manager configuration.
- Product index/show visibility rules.
- Product discounts pricing behavior.
- Vendor registration and vendor product workflows.
- Vendor public storefront visibility and payload behavior.
- Cart flow and checkout order creation.
- Customer order history.
- Admin vendor approvals, admin feedback approvals, and YouTube authorization callback.
- Database schema sanity checks.

## Partial / Gaps vs Architecture Scope

- PayPal wallet approval/capture flow and PayPal direct card checkout are implemented; Stripe remains record/status-only and bank transfer admin verification workflow UI is still missing.
- Shipment, disputes, complaints, and product reports are modeled at DB level but not surfaced as completed user/admin workflows.
- Vendor shipping management and vendor payments/earnings pages are not implemented.
- Admin vendor CRUD beyond pending/approve/reject is not implemented.
- Product moderation workflow UI beyond vendor submission status is not implemented.
- Public informational pages are partially implemented: About, Contact, and Cookie pages remain pending.

## Structural Notes

- Test folder has nested duplication patterns:
  - `tests/Feature/Feature/Admin/FeedbackApprovalTest.php`
  - `tests/Feature/Feature/VendorFeedbackTest.php`
  These tests run, but the directory shape is inconsistent with the rest of the suite.

## Recent Update Notes

- Public-site header now uses a mobile dropdown navigation pattern: menu links are hidden by default on small screens and toggled using a right-aligned 3-bar button.
- Mobile dropdown navigation now opens as an absolute overlay (with spacing and shadow) so page content does not shift downward when the menu is toggled.
- Mobile dropdown navigation visuals were refined to read as navigation rows (not input-like cards), and opening the menu now applies a soft backdrop blur over page content for stronger contrast and focus.
- Production deploy workflow now recreates each release `public/storage` symlink from shared storage directly (instead of `artisan storage:link`) to prevent broken image links in release-based deploys.
- Home page product merchandising was reprioritized for faster first-glance shopping: `welcome` now shows the New Arrivals product grid immediately after the hero section, and the backend feed limit was increased from 4 to 8 approved active products selected for display.
- Home page hero-side Atelier Ledger panel is now hidden on mobile viewports and remains visible on `md+` screens for cleaner first-screen product focus on phones.
- Home page simplification removed the `Atelier Ledger`, `Atelier Voices`, and `Craftsmanship Flow` sections entirely, and the main heritage-marketplace hero now renders as a full-width featured panel.
- The home-page hero was further compacted into a border-free, box-free layout with tighter vertical spacing to bring the `New Arrivals` section higher in the initial scroll.
- GitHub Actions production deploy workflow now creates the release tarball in `/tmp` before moving it to workspace, preventing `tar: .: file changed as we read it` failures during artifact packaging.
- Production deploy script now guards `php artisan view:cache` behind a `resources/views` directory existence check to prevent first-deploy failures when the view path is unavailable at runtime.
- GitHub Actions `lint.yml` and `tests.yml` are temporarily manual-only (`workflow_dispatch`) and no longer auto-run on push/pull_request.
- Production deploy no longer runs `php artisan view:cache`; deployment keeps `config:cache` only to avoid `View path not found` runtime failures during release activation.
- Deploy script now reloads `php8.4-fpm`/`nginx` only when `sudo -n` is available; otherwise it logs and skips reloads to prevent CI failure on password-protected sudo.
- Deployment runbook added at `.ai/knowledge/core/deployment.md` for Hostinger Ubuntu 24.04 production setup (`31.97.51.24`) with Nginx, HTTPS (Let's Encrypt), local-only MariaDB, systemd queue worker, scheduler cron, and GitHub Actions `main` branch CI-build/server-deploy flow.
- `AGENTS.md` now explicitly requires reading `.ai/knowledge/core/deployment.md` before any deployment-related work and treats it as the deployment source of truth unless user overrides.
- Production deployment workflow created at `.github/workflows/deploy-production.yml` to auto-deploy `main` to the VPS using build artifact + SSH release deployment.
- Price display now uses formatted currency labels in major storefront and order views.
- LKR prices render as `Rs. 5,000.00` style formatting instead of raw amount + currency code.
- Checkout now presents PayPal settlement as `1 USD = ... LKR` and requires customer confirmation before wallet or card processing.
- Vendor product edit image deletion now uses corrected Wayfinder argument mapping for `/vendor/products/{product}/images/{image}`.
- Public/buyer-facing commission messaging has been removed; commission details remain visible in vendor product create/edit flows only.
- Dashboard now loads authenticated users' recent order histories via `DashboardController` + `ListDashboardOrderHistories` and displays them in a `/vendor/products`-style card layout with click-to-open detailed order popup.
- Public legal pages are now available at `/privacy-policy` and `/terms-of-service` with Inertia React implementations.
- Shared bottom legal navigation now links Terms of Service and Privacy Policy across public, auth, and app layouts.
- Product show gallery (`/products/{product}`) now supports clickable thumbnail switching, rounded main-image corners, and conditional floating thumbnails that dock only while the selected image bottom remains below the viewport; once the image bottom is visible, thumbnails fall back to inline below the image.
