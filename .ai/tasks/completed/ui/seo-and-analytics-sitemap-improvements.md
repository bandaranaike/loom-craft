# Task: SEO, Analytics, and Sitemap Improvements

## Metadata

- Status: completed
- Created: 2026-07-03
- Updated: 2026-07-03
- Source: follow-up task
- Priority: medium

## Raw Request

User asked to install Google Analytics globally, apply the SEO suggestions, and make the sitemap more complete.

## Objective

Improve the site's search and analytics setup by loading the Google Analytics tag across public pages, keeping private areas out of search indexing, and expanding the sitemap so search engines can discover public products, vendors, and associated images.

## Acceptance Criteria

1. Google Analytics is loaded from the main app template when a tracking ID is configured.
2. Private app routes emit `noindex, nofollow` metadata.
3. `robots.txt` exposes the sitemap and blocks private app areas.
4. `sitemap.xml` includes public pages, active products, approved vendors, and image sitemap entries where available.
5. Feature tests cover the SEO and analytics output.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`

## Likely Implementation Areas

- `resources/views/app.blade.php`
- `config/services.php`
- `routes/web.php`
- `resources/js/components/seo-head.tsx`
- `tests/Feature/SeoAndAnalyticsTest.php`

## Risks Or Open Questions

- Whether sitemap image URLs should include every available image or only primary images.

## Test Plan

- Run the SEO feature test suite.
- Run Pint on the changed PHP files.

## Documentation Updates Required

- None

## Completion Notes

Google Analytics is loaded globally from the app template when configured, private app routes emit `noindex, nofollow`, and `sitemap.xml` now includes image sitemap entries for public products and approved vendors. The SEO feature test suite passes.
