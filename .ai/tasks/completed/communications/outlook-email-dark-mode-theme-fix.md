# Task: Outlook Email Dark Mode Theme Fix

## Metadata

- Status: completed
- Created: 2026-07-02
- Updated: 2026-07-02
- Source: user request
- Priority: high

## Raw Request

- In Outlook dark mode, the email is not showing properly.
- Screenshot: `/var/www/loom-craft/.ai/knowledge/assets/guidelines/OutlookLoomCraftEmailDarkTheme.png`
- Add dark mode CSS.

## Objective

Improve the LoomCraft Markdown email theme so Outlook dark mode keeps readable contrast for background, card, logo/heading, body copy, links, button, and footer content.

## Acceptance Criteria

1. Markdown email CSS includes dark-mode-safe color declarations.
2. Outlook dark mode receives explicit overrides for the email wrapper, body, card, text, links, and buttons.
3. Existing light-mode branding remains intact.
4. Email rendering tests or focused snapshots/assertions verify the CSS contains the dark-mode rules.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `resources/views/vendor/mail/html/themes/default.css`
- `resources/views/vendor/mail/html/*.blade.php`
- `tests/Feature/`

## Risks Or Open Questions

- Outlook has limited and inconsistent dark-mode CSS support, so this should use both media queries and Outlook-specific selectors where possible.

## Test Plan

- Add or update a focused test that checks the mail theme includes the required dark-mode and Outlook override selectors.
- Run focused mail/notification tests.

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented on 2026-07-02.
- Updated Markdown mail layout metadata to advertise `light dark` color scheme support.
- Added dark-mode media query overrides and Outlook-specific `[data-ogsc]` / `[data-ogsb]` selectors for wrapper, body, card, text, links, footer, and primary buttons.
- Added a focused mail theme assertion test.
- Verification: `php artisan test --compact tests/Feature/OrderTrackingTest.php tests/Feature/MailThemeTest.php`.
