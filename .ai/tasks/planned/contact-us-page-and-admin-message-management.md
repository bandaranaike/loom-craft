# Task: Contact Us Page And Admin Message Management

## Metadata

- Status: planned
- Created: 2026-04-20
- Updated: 2026-04-20
- Source: user request
- Priority: high

## Raw Request

We should have a contact-us page. There should be a place to check all the contacts up messages by admin. Name(required), phone, phone and messge. One of email or phone required. message required. Same layout as /login page is good. We may have to create database table for this. Update required documents in .ai folder. Please create a task before start. Ask from me for any clarification

## Objective

Add a public contact-us page using the same general presentation style as the current login page, persist submitted contact messages, and provide an admin-facing area to review those submissions. The form should require a name and message, require at least one contact method between email and phone, and the implementation should include any needed database, validation, admin access, tests, and `.ai` documentation updates.

## Acceptance Criteria

1. A public `contact-us` page exists and follows the existing login-page visual pattern closely enough to feel consistent.
2. The contact form requires `name` and `message`.
3. The contact form requires at least one of `email` or `phone`.
4. Valid submissions are stored in the database.
5. Invalid submissions show validation errors in the UI.
6. An admin-only area exists to review submitted contact messages.
7. Tests cover form validation, persistence, and admin access to submissions.
8. Required `.ai` documentation is updated to reflect the new page, data model, and admin workflow.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/architecture.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `routes/web.php`
- `app/Http/Controllers/`
- `app/Http/Requests/`
- `app/Models/`
- `database/migrations/`
- `resources/js/pages/`
- `resources/js/layouts/`
- `resources/js/components/`
- `tests/Feature/`
- `.ai/knowledge/core/*.md`

## Risks Or Open Questions

- Should the admin review area be a new admin page, part of an existing dashboard, or a simple table view under an existing admin section?
- Should email be stored as optional alongside phone, or was the repeated `phone` in the request intended to include a separate `email` field?
- Do admins need only read access for submissions, or also status management such as read/unread, archived, or replied?
- Should contact submissions trigger an email notification in addition to database storage?

## Test Plan

- Add focused feature tests for public contact form validation rules.
- Add focused feature tests for successful submission persistence.
- Add focused feature tests for admin authorization and submission listing.
- Run the smallest relevant Pest test set with `php artisan test --compact`.

## Documentation Updates Required

- `.ai/knowledge/core/architecture.md`
- `.ai/knowledge/core/db-schema.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

Fill this section only when the task is done.
