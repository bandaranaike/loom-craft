# Task: Mobile Device Session Management Dashboard

## Metadata

- Status: planned
- Created: 2026-04-06
- Updated: 2026-04-06
- Source: user request
- Priority: high

## Raw Request

- I have no clear idea about how the public API dealing with mobile devices.
- If it's possible, create a dashboard page with a sidebar link to see the connected devices.
- If needed, I can revoke the token.

## Objective

Add a dashboard-accessible management view for API-connected mobile devices or sessions so an authorized user can inspect issued mobile access tokens and revoke them when necessary. The task should build on the current Sanctum-based mobile API work rather than duplicating it, and should clarify what "connected devices" can reliably mean with the token data the application currently stores.

## Acceptance Criteria

1. A dashboard sidebar entry exists for the device or API session management page.
2. The page lists active or known mobile API tokens in a way that is understandable to the user.
3. Authorized users can revoke an issued token from the UI.
4. The implementation defines which users can view and revoke tokens and whether they can revoke only their own tokens or all mobile tokens.
5. The UI explains device/session details using the data actually available from Sanctum token records.
6. Focused tests cover page access, token visibility rules, and token revocation.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/tasks/in-progress/mobile-public-api-v1.md`
- `.ai/inbox/api-spec.md`

## Likely Implementation Areas

- `resources/js/components/app-sidebar.tsx`
- A new dashboard Inertia page for device or token management
- `routes/web.php`
- Controller or action classes for listing and revoking tokens
- `app/Models/User.php`
- `database/migrations/2026_04_05_160044_create_personal_access_tokens_table.php`
- `app/Http/Controllers/Api/V1/Auth/LoginController.php`
- Focused feature tests for web authorization and token revocation

## Risks Or Open Questions

- Sanctum personal access tokens do not automatically provide rich device metadata, so the UI may only be able to show token name, abilities, creation time, and last-used time unless additional tracking is added.
- It is not yet defined whether this page should be admin-only, available to vendors for self-managed tokens, or both.
- Revoking a token should not unintentionally disrupt unrelated API clients if multiple sessions share similar device names.

## Test Plan

- Add focused Pest feature tests for the new dashboard page and revoke action
- Verify unauthorized users cannot view or revoke tokens they do not control
- Verify revocation removes or invalidates the targeted personal access token

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/core/best-practices.md`

## Completion Notes

Fill this section only when the task is done.
