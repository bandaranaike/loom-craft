# LoomCraft — Best Practices (Implementation‑Aligned)

These guidelines optimize for LoomCraft’s business logic, architecture, and constraints.

---

## Alignment (Keep In Sync)

This file operationalizes the architecture and must stay aligned with:

- `.ai/knowledge/core/guardrails.md` (hard non-negotiables)
- `.ai/knowledge/core/implementation-guide.md` (concrete implementation guidance)
- `.ai/knowledge/core/db-schema.md` (authoritative domain fields)
- `.ai/knowledge/core/order-process.md` (checkout/order flow)
- `.ai/knowledge/core/implementation-status.md` (code-verified progress)

---

## Stack & Architecture (Non‑Negotiable)
- Laravel 12 (PHP 8.4+), MariaDB
- React + TypeScript via Inertia.js (server‑driven pages)
- PNPM for frontend package management

---

## Core Business Rules (Must Enforce)
- Multi‑vendor marketplace
- Platform commission is configured via `COMMERCE_COMMISSION_RATE` and currently defaults to **100.00%**
- Vendors set a base price; selling price = vendor price + configured commission
- Vendor approval is manual (admin‑controlled)
- Guest checkout is allowed
- Shipping responsibility per order: `vendor` or `platform`
- Refunds are manual only (via disputes)
- Every product must have a vendor-provided `product_code` that is unique across all products
- Every product must also have an application-managed unique `slug` generated from the product name for public URLs
- Product media handling:
  - Images are uploaded to **application storage** and stored as local paths.
  - Videos are uploaded to **YouTube** using the **Google API Client for PHP** and stored as YouTube URLs.

---

## Role & Access Control
- **Guest/Customer**: browse, cart, checkout, pay, complaints/reports
- **Vendor**: access dashboard only after approval
- **Admin**: approvals, moderation, disputes, commission enforcement

Always enforce permissions on the backend (policies/gates/middleware).

---

## Data Integrity & Pricing
- Never accept `selling_price` from user input; calculate from `vendor_price`
- Store commission rate in `COMMERCE_COMMISSION_RATE` and access it through `config('commerce.commission_rate')`
- Public product URLs must use `/product/{slug}`
- Keep currency fixed to `USD`, `EUR`, `LKR` at order/payment level
- Enforce one feedback/suggestion record per authenticated user in application flow (upsert, not duplicate insert)

## Product Media Storage
- Image uploads must use application storage (local filesystem managed by Laravel).
- Video uploads must use YouTube via the Google API Client for PHP; only the resulting YouTube URL is stored.

---

## Controllers, Requests, and Services
- Controllers remain thin; move logic to Actions/Services
- Validation must be in **Form Request** classes
- Prefer Eloquent relationships over raw queries
- Avoid N+1 with `with()` eager loading

---

## Design Patterns & SOLID
- Follow SOLID principles with clear, single‑purpose classes
- Use constructor dependency injection; avoid `new` inside methods
- Prefer a Service/Action layer for business workflows
- Use Repository pattern only when data access becomes complex
- Use DTOs for cross‑layer data transfer when needed

---

## Strict Architecture Rules (Enforced)
- Controllers may only: authorize, validate via Form Requests, call a **single** Action/Service, return Inertia response
- All Action/Service **inputs and outputs must be DTOs** (no raw arrays)
- Controllers must **not** access Eloquent models directly
- Every Action/Service must begin with an explicit **policy check**
- All Action/Service inputs must be **validated** (Form Request or DTO only)
- Use immutable **Value Objects** for money, currency, and dimensions

---

## Inertia & Routing
- All controllers return `Inertia::render(...)`
- Maintain a clear separation between:
  - Public site
  - Vendor dashboard
  - Admin panel
- Use named routes and `route()` helpers for links

## Frontend Styling Convention
- Use Tailwind v4 CSS variable shorthand classes for design tokens:
  - `bg-(--token)`, `border-(--token)`, `ring-(--token)`
  - `text-(--token)` for text color
- Do not use bracket `var(...)` utility form when shorthand form is available.

---

## Laravel 12 Structure
- Middleware are configured in `bootstrap/app.php`
- Do not edit `app/Http/Kernel.php` (not used in Laravel 12)

---

## Testing
- Use Pest for tests
- Every feature change must include or update tests
- Use `RefreshDatabase` for isolation

---

## Prohibited / Guardrails
- No automated refunds
- Do not invent database fields beyond `.ai/knowledge/core/db-schema.md`
- Do not use `env()` outside config files
