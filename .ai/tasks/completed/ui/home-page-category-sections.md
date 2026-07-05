# Task: Home Page Category Sections

## Metadata

- Status: completed
- Created: 2026-07-04
- Updated: 2026-07-04
- Source: user request
- Priority: medium

## Raw Request

Latest note:

- [ ] We need to add category secitons in the home page (/var/www/loom-craft/resources/js/pages/welcome.tsx)
    - One category section can have 3 product items.
    - Max categories count is 5.
    - If the category count less than 5 in the system, then we can show exisiting categories.
    - Every category should have a link to go more products (the link like /products?category=cushion-cover&page=1&per_page=9)
    - So "New Arrivals Shop the latest atelier pieces first." section will be replaced with above.
    - Please keep other contents are same. Please keep the theme consitancy.

## Objective

Replace the homepage new arrivals product strip with category-based product sections that preserve the public site theme, show up to five categories, show up to three visible products per category, and link each category to the public products listing filtered by that category.

## Acceptance Criteria

1. The homepage renders category sections instead of the "New Arrivals" section.
2. At most five categories are shown.
3. Each category section shows at most three active products from approved vendors.
4. Each category section includes a link to `/products` with `category`, `page=1`, and `per_page=9` query parameters.
5. Existing homepage content outside the replaced section remains unchanged.
6. Feature tests cover the category and product limits.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`

## Likely Implementation Areas

- `app/Http/Controllers/HomeController.php`
- `resources/js/pages/welcome.tsx`
- `tests/Feature/HomePageTest.php`

## Risks Or Open Questions

- Categories without public products would render empty sections, so the implementation should only surface categories with visible products.

## Test Plan

- Run `php artisan test --compact tests/Feature/HomePageTest.php`.
- Run frontend type/build verification if available and reasonably scoped.

## Documentation Updates Required

- None expected; this is a localized homepage behavior change already captured in the task file.

## Completion Notes

- Replaced the homepage new arrivals section with category-based product sections.
- Added server-side `category_sections` data with a maximum of five active categories and three visible products per category.
- Added category-specific `/products` links with `category`, `page=1`, and `per_page=9` query parameters.
- Updated homepage feature tests for the new prop shape and limits.
