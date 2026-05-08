# Vendor Public Page Unified Requirements

## Status

- Implementation completed earlier and code-verified on 2026-04-03
- Focused verification on 2026-04-03 confirmed the public vendor storefront, inquiry flow, visibility flags, locations, and vendor profile edit surface are implemented
- `tests/Feature/VendorPublicPageTest.php` passed on 2026-04-03
- `tests/Feature/VendorProfileManagementTest.php` confirms the profile feature surface, but one test currently fails in this environment because the GD extension is not installed for fake image generation

## Objective
Build and enhance the public vendor page as a mini storefront that improves trust, supports vendor storytelling, and drives conversion.

## Primary Outcomes
1. Rich vendor identity and content on the public vendor page.
2. Vendor-related customer-facing price displays use LKR.
3. Strong mobile responsiveness and improved content hierarchy.
4. Safe public visibility handling for contact, website, and media.
5. Vendor profile create flow collects only compulsory fields.
6. Vendor profile edit flow exposes all available vendor database fields.

## Routes
1. Public vendor page:
- Path: `/vendors/{vendor}`
- Suggested name: `vendors.show`
- Lookup strategy:
  - Prefer slug-based resolution (`/vendors/{slug}`) when available.
  - Allow id fallback only if slug is not implemented for all vendors yet.

2. Contact submission endpoint:
- Public POST endpoint for vendor contact form submission.
- Must include validation, CSRF, and rate limiting.

## Required Sections
1. Hero
- Vendor logo.
- Vendor display name.
- Optional tagline.
- Primary CTA: `Contact Vendor`.
- Secondary CTA: `Visit Website` (only when website exists and is public).
- Compact public details block on the right side of the hero:
  - Email
  - Phone
  - WhatsApp
  - Website
  - Visibility/status summary when useful

2. About
- Long-form bio/story content.
- Optional years active.
- Craft specialization summary.
- Trust indicators (for example `Verified Vendor`, `Handcrafted`).

3. Products Showcase
- Must appear directly below the hero as the second main row of the page.
- Grid of vendor products (approved/active only).
- Product card content:
  - Image
  - Name
  - Short description
  - Selling price (LKR formatted)
  - Category label
- Actions:
  - `View Product`
  - Optional quick add-to-cart if aligned with existing flow.

4. Product Category Summary
- Category tiles/chips with product counts.
- In-page filtering of product grid by selected category.

5. Store Locations
- Show all provided vendor locations.
- Per location:
  - Location name
  - Address lines
  - City/region/country
  - Phone (optional)
  - Hours (optional)
  - Google Maps data
- Map behavior:
  - Embedded Google Map iframe is required when embeddable URL is available.
  - Fallback `Open in Google Maps` link if embed URL is unavailable.

6. Contact Form
- Fields:
  - Name
  - Email
  - Subject
  - Message
  - Optional phone
- Behavior:
  - Inline validation errors
  - Success flash message
- Security:
  - Rate limiting
  - Anti-spam baseline (honeypot now, captcha later if needed)

## Data Requirements
1. Vendor profile fields (ensure existence if missing)
- `slug` (unique, preferred public lookup key)
- `tagline`
- `website_url`
- `contact_email`
- `contact_phone`
- `whatsapp_number`
- `logo_path`
- `cover_image_path`
- `about_title`
- `craft_specialties` (JSON)
- `years_active` (unsigned small integer)
- Visibility flags:
  - `is_contact_public` (boolean, default true)
  - `is_website_public` (boolean, default true)

2. Vendor management form behavior
- Create flow:
  - Show only compulsory vendor fields required to successfully create the vendor record.
  - Defer optional enrichment fields until after initial creation.
- Edit flow:
  - Show all vendor fields backed by the database so the vendor/admin can fully manage the public page profile.
  - This includes optional profile, cover image, contact, visibility, locations, and storefront-supporting fields once the record exists.

3. Related data
- `vendor_locations` table for multi-location support.
- Product-category support with:
  - `product_categories`
  - `category_product` pivot
- `vendor_contact_submissions` table for contact form storage.

## Backend Requirements
1. Vendor page payload
- Load vendor, locations, and approved/active products.
- Include product categories and counts.
- Use eager loading to avoid N+1.
- Keep payload lean with pagination/deferred data where beneficial.

2. Public visibility rules
- Vendor must be approved for public page exposure.
- Show only approved/active vendor products.
- Hide website/contact fields when corresponding public flags are false.

3. Currency consistency
- Ensure vendor-page product pricing and related displays format in LKR.

4. URL normalization and validation
- Normalize and validate `website_url` and map URLs.

5. Vendor create/edit form requirements
- Create form must remain minimal and include only required fields.
- Edit form must load and display all persisted vendor fields available in the schema.
- Validation must distinguish required-on-create fields from optional-on-edit enrichment fields where appropriate.

6. Contact form processing
- Validate request via Form Request.
- Persist to `vendor_contact_submissions`.
- Optional notification email if `contact_email` exists and notifications are enabled.

## Frontend Requirements (Inertia + React)
1. New or enhanced page component
- Suggested path: `resources/js/pages/vendors/show.tsx`.

2. UX and states
- Responsive desktop/tablet/mobile layout.
- Empty states for missing products, locations, and contact details.
- Loading/skeleton states for deferred/async blocks.

3. Visual direction
- Premium artisan storefront style.
- Strong typography and clear information hierarchy.
- Accessible contrast, focus states, and semantic structure.
- Keep the hero right column compact.
- Do not visually display the cover image on the public vendor page for now.
- The cover image remains editable/storable in the vendor edit flow only.

## SEO
1. Dynamic title: `{Vendor Name} | LoomCraft`
2. Meta description from vendor bio/tagline.
3. OpenGraph image from logo, with cover image as optional fallback if needed.
4. Canonical URL for vendor page.

## Validation Rules (High-Level)
1. `website_url`, `map_url`: valid URL.
2. `contact_email`: valid email.
3. `contact_phone`, `whatsapp_number`: length + basic format constraints.
4. `years_active`: integer, min 0, max 200.
5. Contact form message length (for example 20 to 2000 chars).

## Performance
1. Paginate product list (default 12 per page).
2. Keep initial payload minimal; use deferred props where useful.

## Security and Privacy
1. Only expose explicitly public vendor data.
2. Sanitize/escape user-entered content in rendering.
3. CSRF protection on contact submission.
4. Rate limit by IP/email for contact endpoint.

## Testing Requirements
1. Feature tests
- Vendor page payload includes required public fields.
- Non-public fields remain hidden.
- Product visibility rules enforce approved/active products only.
- LKR formatting appears correctly on vendor-facing product displays.
- Contact submission validates and stores correctly.

2. UI checks
- Mobile readability and section behavior.
- Map fallback behavior.

## Acceptance Criteria
1. Public can visit vendor page via `/vendors/{slug}` (or configured fallback) and see a complete profile.
2. Vendor-related pricing on public vendor page is formatted in LKR.
3. Product grid shows approved/active items only.
4. Category summary counts and in-page filtering work correctly.
5. Products section appears immediately below the hero as the second main row.
6. Hero right section shows compact public details instead of a separate storefront details section.
7. Cover image is not visually rendered on the public vendor page.
8. Locations render correctly with map embed or fallback link.
9. Contact details/website obey visibility flags.
10. Contact form is secure, validated, and stores submissions.
11. Page is responsive, accessible, and free from main payload N+1 issues.
12. Vendor create page shows only compulsory fields needed to create the vendor.
13. Vendor edit page shows all vendor fields stored in the database.

## Suggested Implementation Phases
1. Database
- Add missing vendor profile/visibility fields.
- Add or finalize `vendor_locations`, `product_categories`, `category_product`, and `vendor_contact_submissions`.

2. Backend
- Build/adjust vendor show action and route.
- Add contact submission endpoint and Form Request.
- Enforce LKR formatting behavior in vendor-facing product data.

3. Frontend
- Implement/enhance Inertia vendor page sections.
- Add category filtering behavior and contact form UX.
- Keep public details compact within the hero right column.
- Do not include a public media gallery section.

4. Testing
- Add feature tests for visibility, product filtering, contact flow, and LKR formatting.
- Add responsive/manual UI checks for key breakpoints.

## Open Decisions
1. URL policy:
- Slug-only routing now, or temporary id fallback support?

2. Category policy:
- Global categories or vendor-scoped categories?

3. Contact notifications:
- Store-only in v1, or immediate vendor email notifications?

4. Moderation:
- Do contact submissions require admin moderation in v1?

## Completion Notes

- Public vendor pages are available at `/vendors/{slug}` and render through `vendors/show`
- Visibility flags hide website and contact information correctly when disabled
- Vendor locations, category summaries, product filtering, and inquiry submission are implemented
- Vendor registration remains minimal while vendor profile edit exposes the broader public storefront dataset
