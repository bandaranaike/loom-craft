# LoomCraft — Best Practices (Implementation‑Aligned)

These guidelines optimize for LoomCraft’s business logic, architecture, and constraints.

---

## Alignment (Keep In Sync)

This file operationalizes the architecture and must stay aligned with:

- `.ai/guardrails.md` (hard non-negotiables)
- `.ai/implementation.md` (concrete implementation guidance)
- `.ai/dbschema.md` (authoritative domain fields)
- `.ai/order-process.md` (checkout/order flow)
- `.ai/implementation-status.md` (code-verified progress)

---

## Stack & Architecture (Non‑Negotiable)
- Laravel 12 (PHP 8.4+), MariaDB
- React + TypeScript via Inertia.js (server‑driven pages)
- PNPM for frontend package management

---

## Core Business Rules (Must Enforce)
- Multi‑vendor marketplace
- Platform commission is **always 7%**
- Vendors set a base price; selling price = vendor price + 7%
- Vendor approval is manual (admin‑controlled)
- Guest checkout is allowed
- Shipping responsibility per order: `vendor` or `platform`
- Refunds are manual only (via disputes)
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
- Store commission rate as `7.00` and derive commission amounts from it
- Keep currency fixed to `USD`, `EUR`, `LKR` at order/payment level

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
- Do not invent database fields beyond `.ai/dbschema.md`
- Do not use `env()` outside config files
