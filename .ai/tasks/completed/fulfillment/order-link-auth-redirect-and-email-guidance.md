# Task: Order Link Auth Redirect And Email Guidance

## Metadata

- Status: completed
- Created: 2026-07-02
- Updated: 2026-07-02
- Source: user request
- Priority: high

## Raw Request

- The order showing page (`/orders/{orders.public_id}`) gives a 403 Permission Required / Access Restricted warning.
- Either this page should be public or it should redirect to the login page and once logged in, return to the showing page.
- The email should explain that the user has to login to the account to access the link.

## Objective

Replace the guest 403 experience for protected customer order links with an authentication redirect that preserves the intended order URL, and update order email copy so recipients understand they may need to log in before viewing the order.

## Acceptance Criteria

1. A guest visiting a customer-owned order URL is redirected to login instead of seeing a 403 page.
2. After login, Laravel returns the user to the intended order URL.
3. Guest checkout session access remains available for guest orders where the session owns the order.
4. Authenticated users who do not own the order remain forbidden.
5. Order notification emails mention that account login may be required to view the link.
6. Focused tests cover guest redirect, intended URL preservation, and unauthorized authenticated access.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `app/Actions/Order/ShowOrder.php`
- `app/Notifications/OrderCustomerNotification.php`
- `tests/Feature/OrderTrackingTest.php`

## Risks Or Open Questions

- Public access would expose order details to anyone with the link, so the safer default is login redirect with intended URL.

## Test Plan

- Add focused feature tests for guest redirect to login and authenticated non-owner 403 behavior.
- Run focused order tracking and notification tests.

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented on 2026-07-02.
- Guest visits to account-owned order links now redirect to login and preserve the intended order URL.
- Guest checkout orders remain viewable by public order ID, while payment proof uploads still require guest session ownership.
- Authenticated non-owners remain forbidden.
- Order customer emails now mention that account login may be required.
- Verification: `php artisan test --compact tests/Feature/OrderTrackingTest.php tests/Feature/MailThemeTest.php`.
