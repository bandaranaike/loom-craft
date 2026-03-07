# Task: Vendor Public Page Enhancement

## Goal
Improve vendor public profile pages to show richer vendor identity/content and ensure storefront pricing/labels use LKR.

## Why
- Increases trust and buyer confidence.
- Supports vendor storytelling and conversion.

## Scope
1. Ensure vendor-related customer-facing price displays are LKR (not USD).
2. Enhance vendor page with:
- Logo at top
- Long-form description/about
- Photo/video section
- Location details with Google Maps links/embed support
- Website link (when available)

3. Improve layout/content hierarchy and mobile responsiveness.
4. Map requirement confirmed: use embedded Google Maps on vendor page (not link-only).

## Data Requirements
1. Vendor profile fields (if missing)
- `logo_path`
- `description`
- `website_url`
- `location_name/address/map_url`
- media relations for photos/videos

2. Visibility controls (recommended)
- Flags for public website/contact/media visibility.

## Backend Work
1. Vendor show payload
- Eager load vendor media and location data.
- Normalize website/map URLs.

2. Currency consistency
- Ensure currency formatter uses LKR for vendor page price components.

3. Validation
- Website/map URLs valid.
- Media types constrained.

## Frontend Work
1. Vendor hero
- Logo, name, core trust/meta info.

2. About + media
- Story block + media gallery.
- Video cards/embeds where available.

3. Location module
- Address details + embedded Google Map iframe.
- Keep fallback “Open in Google Maps” link if embed URL is unavailable.

4. External links
- Website with safe target handling.

## UX Suggestions
1. Story-first layout with clear CTA to browse products.
2. Vendor credibility row (years active, approved status, specialties).
3. Reusable media card style matching site theme.
4. Graceful empty states when vendor has missing data.

## Testing
1. Feature tests
- Vendor page payload includes new public fields.
- Non-public fields remain hidden.
- LKR formatting appears correctly in vendor-facing product sections.

2. UI checks
- Mobile layout readability and media behavior.

## Acceptance Criteria
1. Vendor page displays richer content and branding.
2. Website and map links work correctly.
3. Vendor-related pricing displays LKR.
4. Page remains responsive and aligned with public-site design language.
