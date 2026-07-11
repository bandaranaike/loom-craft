# Task: Naturesnature Multi-Site Theme And Deployment

## Metadata

- Status: in-progress
- Created: 2026-07-09
- Updated: 2026-07-09
- Source: user request
- Priority: high

## Raw Request

Use the same codebase with different deployment. Do not package backend functionality into Composer packages for now. Plan minimal changes and minimal Codex token quota. The admin
and public website must support completely different themes, and some web pages such as home, product listing, and product show pages can have different layouts. The second website
name is `naturesnature`, for organic homemade foods such as cookies. It should use orange, brown, yellow, and red colors, feel modern and rich, and take inspiration from the
provided Beaver Creek Dairy reference image while allowing UI/UX freedom to match the new concept.

## Objective

Introduce a low-risk multi-site structure that allows `loomcraft.work` and `naturesnature.store` to run from the same Laravel/Inertia codebase as separate deployments, with separate
environment configuration, database/storage, branding, public theme, admin theme, and selected site-specific page layouts. Reuse all backend commerce, checkout, order, vendor,
auth, admin, and fulfillment functionality without extracting Composer packages at this stage.

## Acceptance Criteria

1. The application can determine the active site from configuration, using an environment value such as `APP_SITE=loomcraft` or `APP_SITE=naturesnature`.
2. Shared site metadata is available to Laravel and Inertia, including site key, name, domain, logo paths, theme key, and primary brand colors.
3. Existing Loom Craft behavior and visual appearance remain unchanged when `APP_SITE=loomcraft`.
4. `naturesnature` has a distinct public theme using a warm organic food palette: orange, brown, yellow, red, cream, and deep green accents where useful.
5. `naturesnature` has a distinct admin/backend theme so authenticated dashboards and admin/vendor screens do not look like Loom Craft.
6. The public layout supports site-specific navigation labels, logo, footer content, and brand copy without duplicating route logic.
7. The home page supports a site-specific layout for `naturesnature`, inspired by the reference image: rich editorial hero, food product imagery, category panels, curated/gifting
   section, and warm cream section bands.
8. The product listing page can use a `naturesnature`-specific presentation while reusing the existing product data, filters, pagination, and Wayfinder route functions.
9. The product show page can use a `naturesnature`-specific presentation while reusing the existing product detail data, reviews, cart actions, variations, and checkout path.
10. Backend controllers, models, Form Requests, policies, actions, order logic, checkout integrations, and admin workflows are not forked or duplicated.
11. Loom Craft-specific public surfaces, copy, navigation items, and feature links are hidden or replaced for `naturesnature`, including the loom weave demo and
    woven/craft-specific language.
12. The deployment plan documents that each domain should use a separate `.env`, database, storage path, cache prefix, queue worker, scheduler, mail credentials, payment
    credentials, and web server virtual host.
13. Tests verify that the active site config is shared with Inertia and that Loom Craft remains the default/fallback site.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/architecture.md`
- `.ai/knowledge/core/deployment.md`
- `.ai/knowledge/core/mobile-theme-guidelines.md`
- `.ai/knowledge/assets/guidelines/91e51f765daa5d2740b837aeac8eef65.webp`

## Likely Implementation Areas

- `config/sites.php`
- `.env.example`
- `app/Support/Site.php` or similar small site context helper
- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/types/index.ts`
- `resources/js/app.tsx`
- `resources/css/app.css`
- `resources/css/themes/loomcraft.css`
- `resources/css/themes/naturesnature.css`
- `resources/js/layouts/public-site-layout.tsx`
- `resources/js/layouts/app-layout.tsx`
- `resources/js/layouts/app/app-sidebar-layout.tsx`
- `resources/js/layouts/app/app-header-layout.tsx`
- `resources/js/components/app-logo.tsx`
- `resources/js/components/app-logo-icon.tsx`
- `resources/js/components/app-sidebar.tsx`
- `resources/js/pages/welcome.tsx`
- `resources/js/pages/products/index.tsx`
- `resources/js/pages/products/show.tsx`
- Optional targeted split components:
    - `resources/js/sites/loomcraft/pages/home.tsx`
    - `resources/js/sites/naturesnature/pages/home.tsx`
    - `resources/js/sites/naturesnature/components/food-product-card.tsx`
    - `resources/js/sites/naturesnature/components/naturesnature-admin-shell.tsx`
- `tests/Feature/SiteConfigurationTest.php`
- Existing focused page tests as needed:
    - `tests/Feature/HomePageTest.php`
    - `tests/Feature/ProductIndexTest.php`
    - `tests/Feature/ProductShowTest.php`
    - `tests/Feature/DashboardTest.php`

## Minimal Implementation Plan

1. Add `config/sites.php` with `loomcraft` and `naturesnature` entries, and read `APP_SITE` from config with `loomcraft` as the safe default.
2. Add a small site context helper/service so controllers and middleware do not read `env()` directly.
3. Share the active site payload through `HandleInertiaRequests`, keeping the payload compact and stable.
4. Add TypeScript types for `page.props.site`.
5. Add a root theme attribute or class, such as `data-site-theme="naturesnature"`, at the Inertia app/root layout level.
6. Move current Loom Craft public color variables into an explicit Loom Craft theme layer only if needed; otherwise preserve existing variables and add a separate `naturesnature`
   override layer.
7. Add `naturesnature` CSS variables using Tailwind v4-compatible CSS-first tokens:
    - cream background
    - burnt orange primary
    - cocoa brown text
    - golden yellow accents
    - tomato/red highlight
    - restrained deep green support accent
8. Update shared logos/navigation/footer to read from site config while keeping existing routes and Wayfinder imports.
9. Add a site-aware home page composition that uses a `naturesnature` branch/component only for layout and presentation, not data loading.
10. Add site-aware product listing and product show presentation branches only where the layout truly needs to differ.
11. Add site-aware navigation/content filtering so `naturesnature` does not expose Loom Craft-specific pages or copy.
12. Skin authenticated/admin layouts through theme variables first. Only introduce `naturesnature` admin components if CSS variables cannot produce enough separation.
13. Use placeholder food/product imagery for the first implementation pass until final `naturesnature` assets are available.
14. Add focused feature tests for site config and Inertia shared props.
15. Run targeted PHP tests and frontend type/build checks.

## UI Direction For Naturesnature

- Brand feel: modern, warm, premium, homemade, organic, rich, and food-focused.
- Visual reference: use the provided Beaver Creek Dairy image as mood direction, not as a direct copy.
- Palette: cream base, cocoa brown typography, burnt orange buttons, golden yellow highlights, tomato/red small accents, with optional muted green for organic cues.
- Homepage direction:
    - editorial food hero with strong product imagery
    - rounded product/category tiles, but avoid overusing nested cards
    - cream and warm color bands with soft separators
    - featured cookies/organic foods/product bundles
    - curated gifting or homemade boxes section
    - clear shop/category actions
- Admin direction:
    - still operational and scannable
    - warmer header/sidebar treatments
    - compact tables and forms remain practical
    - no marketing-style admin screens

## Deployment Plan

- Deploy the same repository to two app directories or release directories on the same Ubuntu 24.04 Hostinger server.
- Use one Nginx server block per domain:
    - `loomcraft.work`
    - `naturesnature.store`
- Use separate `.env` files:
    - `APP_SITE=loomcraft`
    - `APP_SITE=naturesnature`
- Use separate databases and separate admin/user accounts.
- Use separate storage directories or separate deployment directories with their own `storage/app/public`.
- Use separate cache prefixes, session cookies, queues, mail credentials, payment credentials, and public storage symlinks.
- Run separate queue workers and scheduler entries per deployment.
- Keep deployment commands the same for both sites: Composer install, build frontend assets, migrate the relevant database, cache config/routes/views, restart PHP-FPM/queue
  workers.

## Decisions And Remaining Risks

- Confirmed: `naturesnature.store` should hide Loom Craft-specific features, including custom woven product language and the loom weave demo.
- Confirmed: each site should use separate databases and separate admin/user accounts.
- Confirmed: placeholder food imagery is acceptable for the first implementation pass.
- Need final logo, brand name spelling/capitalization, and tagline for `naturesnature`.
- Existing public/auth/admin pages use many `--welcome-*` theme variables, so the fastest path is theme variable overrides rather than rewriting every component.
- Final product images for the food concept should replace placeholders before production launch.
- Legal pages, emails, SEO metadata, and transactional copy may need site-specific text beyond pure theme work.

## Test Plan

- Run `php artisan test --compact tests/Feature/SiteConfigurationTest.php`.
- Run `php artisan test --compact tests/Feature/HomePageTest.php`.
- Run `php artisan test --compact tests/Feature/ProductIndexTest.php tests/Feature/ProductShowTest.php` if product page layout props change.
- Run `php artisan test --compact tests/Feature/DashboardTest.php` if admin layout shared props or navigation change.
- Run `pnpm run build` after frontend changes.
- Use browser verification for both themes:
    - `APP_SITE=loomcraft`
    - `APP_SITE=naturesnature`
    - desktop and mobile viewports for home, products index, product show, login, dashboard, and admin order/product pages.

## Documentation Updates Required

- `.ai/knowledge/core/architecture.md`
- `.ai/knowledge/core/deployment.md`
- `.ai/knowledge/core/best-practices.md` only if new site/theme conventions should become mandatory for future work.

## Progress Notes

- Added `APP_SITE` driven site configuration for `loomcraft` and `naturesnature`.
- Shared compact active-site metadata through Inertia.
- Added root `data-site` and `data-site-theme` attributes for frontend theme switching.
- Added a warm `naturesnature` Tailwind/CSS variable theme for public and authenticated/admin surfaces.
- Added a `naturesnature` homepage branch with placeholder food imagery and organic homemade food positioning.
- Made public layout branding/navigation/footer site-aware and hid the loom design footer link for `naturesnature`.
- Hid the loom weave demo route with a 404 for `naturesnature` and removed it from the sitemap for sites that hide loom features.
- Made products index/show and dashboard/admin navigation use selected site labels for the main visible brand/domain language.
- Added naturesnature-specific product listing and product detail layouts that remove all color filters/swatches and keep the food site visually distinct.
- Added tests covering naturesnature product list/detail color stripping and restored LoomCraft config isolation in the product feature suite.
- Split deployment workflows into `.github/workflows/deploy-loomcraft.yml` and `.github/workflows/deploy-naturesnature.yml`.
- Added focused feature tests for site config sharing and loom demo hiding.

## Completion Notes

Fill this section only when the task is done.
