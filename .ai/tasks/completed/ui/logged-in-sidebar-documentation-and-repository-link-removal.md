# Task: Logged In Sidebar Documentation And Repository Link Removal

## Metadata

- Status: completed
- Created: 2026-07-02
- Updated: 2026-07-02
- Source: user request
- Priority: low

## Raw Request

- The 'Doucmentation' and the 'Repository' links of the logged in side menu is not required.

## Objective

Remove the logged-in sidebar footer links for Documentation and Repository so the authenticated navigation only shows application-relevant controls. The change should not leave empty spacing, layout regressions, or dead links in the authenticated shell.

## Acceptance Criteria

1. The logged-in sidebar no longer shows Documentation or Repository links.
2. The authenticated sidebar layout still renders cleanly on desktop and mobile widths after the links are removed.
3. No dead footer actions remain in the logged-in sidebar.
4. Any shared authenticated navigation rendering those links is updated consistently.
5. A focused test or render check covers the authenticated layout after the removal.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `resources/js/components/app-sidebar.tsx`
- `resources/js/components/app-header.tsx`
- `resources/js/components/nav-footer.tsx`
- `resources/js/layouts/`
- `tests/Feature/`

## Risks Or Open Questions

- None remaining for the authenticated React shell.

## Test Plan

- Add or update a focused render or feature test for the authenticated layout.
- Run the smallest relevant test set with `php artisan test --compact`.

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented on 2026-07-02.
- Removed Repository and Documentation links from the logged-in sidebar footer.
- Removed the matching starter-kit links from the authenticated header/mobile menu so the shell is consistent.
- Verified with TypeScript and production Vite build.
