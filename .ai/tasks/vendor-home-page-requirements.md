# Vendor Public Home Page Requirements

## Objective
Build a public vendor home page that presents each vendor as a mini storefront with trust-building details and clear contact options.

## Primary Route
- Public route per vendor: `/vendors/{vendor}`
- Route name suggestion: `vendors.show`
- Vendor lookup:
  - Prefer a unique slug (new column) for friendly URLs
  - Fallback to vendor id if slug is not yet implemented

## Core Sections (Required)
1. Hero Section
- Vendor display name
- Cover image or branded color/texture background
- Short tagline (optional)
- Primary CTA: `Contact Vendor`
- Secondary CTA: `Visit Website` (only if website exists)

2. About Us
- Vendor bio/story
- Years active (optional)
- Craft specialization summary
- Trust badges (e.g., `Verified Vendor`, `Handcrafted`, `Sustainable`)

3. Products Showcase
- Grid of vendor products (only approved/active products)
- Card content:
  - Product image
  - Product name
  - Short description
  - Price (selling price)
  - Category label
- Product actions:
  - `View Product`
  - Optional quick add-to-cart (if consistent with existing product flow)

4. Product Category Summary
- Summary tiles/chips showing category name + product count
- Clicking a category filters product grid in-page

5. Store Locations
- Show all physical/store/atelier locations (if provided)
- For each location:
  - Location name
  - Address lines
  - City/region/country
  - Phone (optional)
  - Open hours (optional)
  - Google Maps URL (optional)

6. Contact Details
- Public contact details (only if provided by vendor/admin-approved):
  - Email
  - Phone
  - WhatsApp (optional)
  - Website URL
  - Social links (optional)

7. Small Contact Us Form
- Fields:
  - Name
  - Email
  - Subject
  - Message
- Optional: phone
- Submission result:
  - Success flash message
  - Validation errors inline
- Security:
  - Rate limiting
  - Spam prevention (honeypot or captcha later)

## Additional Recommended Sections
1. Featured Products Carousel
- Up to 6 highlighted products selected by vendor/admin

2. Vendor Stats Row
- Total products
- Categories count
- Average rating (future-ready)
- Total completed orders or crafts delivered (if available)

3. FAQ Snippet
- 3 to 5 common questions (shipping, custom orders, lead time)

4. Related Vendors
- Optional section showing similar vendors by craft type/location

## Data Model Changes

### A) `vendors` table additions
Add nullable columns:
- `slug` (unique)
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
- `is_contact_public` (boolean default true)
- `is_website_public` (boolean default true)

### B) New table: `vendor_locations`
Columns:
- `id`
- `vendor_id` (FK)
- `location_name`
- `address_line_1`
- `address_line_2` (nullable)
- `city`
- `region` (nullable)
- `postal_code` (nullable)
- `country`
- `phone` (nullable)
- `hours` (nullable)
- `map_url` (nullable)
- `is_primary` (boolean default false)
- timestamps

### C) New table: `product_categories`
Columns:
- `id`
- `name`
- `slug` (unique)
- `description` (nullable)
- `is_active` (boolean default true)
- timestamps

### D) New pivot table: `category_product`
Columns:
- `id`
- `product_id` (FK)
- `product_category_id` (FK)
- timestamps
- Unique index on (`product_id`, `product_category_id`)

### E) New table: `vendor_contact_submissions`
Columns:
- `id`
- `vendor_id` (FK)
- `name`
- `email`
- `phone` (nullable)
- `subject`
- `message`
- `status` (default: `new`)
- `submitted_at`
- timestamps

## Backend Requirements
1. Public vendor page controller/action
- Load vendor + approved products + media + categories + locations
- Eager load relationships to avoid N+1

2. Public vendor page data rules
- Vendor must be approved
- Products shown must be approved/active only
- Hide contact details if corresponding visibility flags are false

3. Contact form handling
- Store submission in `vendor_contact_submissions`
- Optional email notification to vendor if `contact_email` is available
- Apply request validation and rate limiting

4. Optional admin/vendor management
- Ability for vendor to update public profile fields
- Ability to manage multiple locations
- Ability to assign categories to products

## Frontend Requirements (Inertia + React)
1. New page component
- Suggested file: `resources/js/pages/vendors/show.tsx`

2. UX behavior
- Responsive layout for desktop/tablet/mobile
- Skeletons/loading states for async/deferred blocks
- Empty states for:
  - No products
  - No locations
  - No contact details

3. Visual style direction
- Premium artisan storefront look
- Strong typography hierarchy
- Distinct but subtle backgrounds/texture blocks
- Accessible contrast and focus states

## SEO & Metadata
- Dynamic page title: `{Vendor Name} | LoomCraft`
- Meta description from vendor bio/tagline
- OpenGraph image from cover/logo
- Canonical URL for vendor page

## Validation Rules (High Level)
- `website_url`: valid URL
- `contact_email`: valid email
- `contact_phone`, `whatsapp_number`: max length with basic format validation
- `years_active`: integer, min 0, max 200
- `map_url`: valid URL
- Contact form message length limits (e.g., 20 to 2000 chars)

## Performance Requirements
- Paginate products (default 12 per page)
- Optimize image loading (`loading="lazy"` where appropriate)
- Keep initial page payload lean with deferred data where useful

## Security & Privacy
- Only show public vendor data
- Escape/sanitize user-entered text in rendering pipeline
- CSRF protection on contact form
- Rate limit contact submissions by IP/email

## Acceptance Criteria
1. Public can visit `/vendors/{slug}` and view vendor profile.
2. Product grid shows only approved/active vendor products.
3. Category summary displays correct counts and filters products correctly.
4. Locations appear only when available.
5. Contact details and website links appear only when provided and public.
6. Contact form validates input and stores submission successfully.
7. Page is responsive and accessible on mobile and desktop.
8. No N+1 query issues for main page payload.

## Implementation Phases
1. Database phase
- Migrations for vendor profile fields + new tables
- Model relationships + fillables/casts

2. Backend phase
- Public vendor page route/controller/action
- Contact form endpoint + request class

3. Frontend phase
- Inertia page UI and interactions
- Category filtering and sections

4. Test phase
- Feature tests for page visibility rules and contact submission
- Validation tests for contact form and profile fields

## Open Decisions Needed
1. URL strategy:
- Use `/vendors/{slug}` only, or support `/vendors/{id}` fallback?

2. Product category model:
- Should categories be global across platform, or vendor-specific?

3. Contact form delivery:
- Store-only for now, or send vendor email notifications immediately?

4. Moderation:
- Do contact submissions need admin moderation tools in the first release?

5. Public visibility defaults:
- Should contact details and website be public by default for approved vendors?

---
Prepared for implementation of a new vendor home page experience with product showcase, about section, category summary, location details, and lightweight lead capture.
