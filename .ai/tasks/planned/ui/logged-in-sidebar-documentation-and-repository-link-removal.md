# Task: Logged In Sidebar Documentation And Repository Link Removal

## Metadata

- Status: planned
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

- The same link set also appears in the authenticated header component, so the intended scope should be verified before removing anything beyond the sidebar.
- If the footer navigation is shared, the change may need a small structural cleanup rather than a simple item deletion.

## Test Plan

- Add or update a focused render or feature test for the authenticated layout.
- Run the smallest relevant test set with `php artisan test --compact`.

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
