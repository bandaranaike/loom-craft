# **Implementation Guide — LoomCraft**

This document translates the approved architecture into concrete implementation guidance. It stays within the defined scope and avoids inventing business rules not present in the architecture brief.

---

## Alignment (Keep In Sync)

- `.ai/guardrails.md` (hard non-negotiables)
- `.ai/best-practices.md` (implementation rules)
- `.ai/dbschema.md` (authoritative domain fields)
- `.ai/order-process.md` (checkout/order flow)
- `.ai/implementation-status.md` (code-verified progress)

---

## 1. Stack & Constraints (Non‑Negotiable)

- Backend: Laravel
- Frontend: React + TypeScript
- Adapter: Inertia.js (server‑driven pages)
- Package manager: PNPM
- Database: MariaDB

---

## 2. Core Domain Rules (Must Implement As‑Is)

- Multivendor marketplace
- Vendors set a base product price
- Platform applies fixed **7% commission**
- Product creation must show:
  - Vendor price
  - Selling price (Vendor price + 7%)
- Vendor approval is **manual** (admin controlled)
- Guest checkout is allowed
- Shipping responsibility is configurable per order:
  - Vendor‑handled
  - Platform‑handled
- Product media handling:
  - Images are uploaded to **application storage** and stored as local paths.
  - Videos are uploaded to **YouTube** using the **Google API Client for PHP** and stored as YouTube URLs.

---

## 3. Roles & Access Control

### Guest / Customer
- Browse, view details, add to the cart, checkout, pay
- Submit complaints and product reports
- If authenticated, submit and edit one home-page feedback entry

### Vendor
- Requires admin approval before selling
- Access vendor dashboard post‑approval
- Manage products, orders, shipping responsibility, payments/earnings
- Participate in dispute handling
- Submit and edit one home-page feedback entry

### Admin
- Full access
- Vendor CRUD + approval/verification
- Product moderation
- Commission enforcement
- Order + dispute resolution
- Complaint/report management
- Site improvement suggestions

**Implementation note:** enforce permissions on the backend. All vendor/admin pages must be protected server‑side.

---

## 4. Required Pages & Inertia Mappings

### Public Pages
- Home
  - Shows approved feedback entries
  - For authenticated vendor/customer users, includes a feedback form that creates or updates a single user-owned entry
- Product listing
- Product details (images + optional video)
- Vendor profile
- Cart
- Checkout
- Order confirmation
- About us
- Contact us
- Terms & conditions
- Privacy policy
- Cookie policy

### Authentication Pages
- Login
- Register

### Vendor Pages (Protected)
- Dashboard overview
- Product management
- Order management
- Shipping management
- Payments & earnings
- Dispute participation

### Admin Pages (Protected)
- Dashboard
- Vendor management (CRUD + approval)
- Product moderation
- Order & dispute management
- Complaints & product reports
- Site improvement suggestions

**Implementation note:** all Laravel controllers must return `Inertia::render(...)`.

---

## 5. Product Data Model Requirements (Minimum Fields)

- Product name
- Description
- Vendor price
- Platform commission (7%)
- Selling price
- Materials used
- Number of pieces produced
- Production time
- Dimensions
- Product images
- Optional product video

**Implementation note:** selling price should be derived from vendor price and fixed commission (not user‑editable).
**Implementation note:** store image paths in application storage; store video as a YouTube URL created via Google API Client for PHP.

---

## 6. Payments & Currency

### Payment Methods
- Stripe
- Manual bank transfer (admin‑verified)
- Cash on delivery

### Supported Currencies
- USD
- EUR
- LKR

### Refund Policy
- Manual refunds only via disputes

---

## 7. Routing & Structure Guidance (High‑Level)

### Separation of Areas
- Public site
- Vendor dashboard
- Admin panel

### Route Protection
- Public routes accessible to guests
- Vendor routes require approved vendor role
- Admin routes require admin role

### Inertia Organization
- React components organized by domain and page
- Page components live in `resources/js/Pages`

---

## 8. Non‑Goals / Explicit Boundaries

- Do not change dependencies without approval

---

## 9. Feedback Workflow Rules (Home Page)

- Feedback is authenticated-only for `vendor` and `customer` roles.
- One feedback record per user is enforced in application flow (create-or-update behavior).
- Editing an existing feedback entry is done in place from the same home-page form.
- Guest feedback submission is out of scope for now.

---

If any part of the implementation requires deeper specification (e.g., exact data relationships, order workflow, dispute lifecycle), confirm before proceeding.
