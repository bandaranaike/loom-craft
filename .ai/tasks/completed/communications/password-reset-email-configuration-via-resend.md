# Task: Password Reset Email Configuration Via Resend

## Metadata

- Status: completed
- Created: 2026-04-04
- Updated: 2026-04-04
- Source: user request
- Priority: high

## Raw Request

- Application email should be configured to send emails when the password reset.
- https://resend.com/

## Objective

Configure the application so password reset emails are sent reliably through Resend, including the required mail transport setup, environment configuration, and any Laravel or Fortify password reset notification wiring needed for the existing authentication flow.

## Acceptance Criteria

1. Password reset requests trigger an email delivery path that uses Resend.
2. Required environment variables and sender configuration are clearly identified.
3. The task covers whether the integration should use SMTP or Resend's API-backed Laravel mail transport.
4. The implementation preserves the current Laravel Fortify password reset flow unless a deliberate customization is required.
5. The work includes verification that password reset notifications are queued or sent correctly in the current app environment.
6. Any required deployment or domain-level email prerequisites are captured before rollout.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/implementation-guide.md`
- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/core/deployment.md`

## Likely Implementation Areas

- `config/mail.php`
- `.env` and `.env.example`
- Password reset notification flow used by Laravel Fortify
- User model notification overrides if default mail content or channel needs customization
- Queue/mail delivery configuration for non-local environments

## Risks Or Open Questions

- It is not yet specified whether Resend should be integrated via SMTP credentials or a package / API transport.
- The verified sender domain, from-address, and from-name are not yet documented.
- It is not yet clear whether password reset emails should use Laravel's default notification template or a custom branded message.
- If mail sending is queued, queue workers must already be configured in the target environment.

## Test Plan

- Add or update a focused feature test proving a password reset request dispatches the expected notification
- Verify the app resolves the intended mailer configuration in the test environment
- Perform a manual password reset request in a non-production environment after configuration is in place

## Documentation Updates Required

- `.ai/knowledge/core/implementation-status.md`
- `.ai/knowledge/core/deployment.md`

## Completion Notes

- Added `App\Notifications\Auth\ResetPasswordNotification` as a thin extension of Laravel's built-in reset notification and pinned its mail delivery to the `resend` mailer.
- Overrode `App\Models\User::sendPasswordResetNotification()` so Fortify password reset requests now dispatch the Resend-backed notification without changing the existing reset flow or URLs.
- Updated `.env.example` to document the required Resend variables and default sender values for password reset delivery.
- Published and customized Laravel's mail Markdown components under `resources/views/vendor/mail` so email layout now uses LoomCraft branding, logo, and a visible text wordmark fallback when remote images are blocked.
- Verified the flow with `php artisan test --compact tests/Feature/Auth/PasswordResetTest.php`.
