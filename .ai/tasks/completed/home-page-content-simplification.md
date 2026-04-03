# Task: Home Page Content Simplification

## Metadata

- Status: completed
- Created: 2026-04-03
- Updated: 2026-04-03
- Source: user request
- Priority: medium

## Raw Request

- 'Atelier Voices' section of the home page should be removed.
- 'Craftsmanship Flow' section should be removed from the home page
- We can remove the "Atelier Ledger" section from the home page. The section "A marketplace devoted to Sri Lanka's most rare woven luxury." it can be taken full width.

## Objective

Simplify the home page by removing lower-priority editorial sections and giving the core hero message more visual weight, so the storefront feels cleaner and more product-focused.

## Acceptance Criteria

1. The `Atelier Voices` section is removed from the home page.
2. The `Craftsmanship Flow` section is removed from the home page.
3. The `Atelier Ledger` section is removed from the home page.
4. The hero section containing `A marketplace devoted to Sri Lanka's most rare woven luxury.` expands to a full-width layout after the side content is removed.
5. The revised home page remains responsive on mobile and desktop.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-guide.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `resources/js/pages/welcome*`
- Any shared public-site home page components
- Home page feature tests or route-render tests

## Risks Or Open Questions

- The exact boundaries of the home page sections should be confirmed in the current React page structure before implementation.
- If any removed section currently provides authenticated feedback entry points, replacement behavior may need an explicit product decision.

## Test Plan

- Run focused public page tests covering the home page
- Run a frontend build or relevant type checks if the page structure changes materially

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implementation completed on 2026-04-03
- Verified with focused Pest coverage, Pint, and TypeScript checks on 2026-04-03
- Removed the home-page `Atelier Voices`, `Craftsmanship Flow`, and `Atelier Ledger` sections
- Expanded the hero area into a full-width featured panel and removed the now-unused controller props feeding the deleted sections
- Follow-up refinement on 2026-04-03 removed the hero panel border, background box, and shadow treatment, and tightened section spacing so `New Arrivals` sits higher on the page
