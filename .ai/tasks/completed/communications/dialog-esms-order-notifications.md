# Task: Dialog ESMS Order Notifications

## Metadata

- Status: completed
- Created: 2026-07-02
- Updated: 2026-07-02
- Source: user request
- Priority: high

## Raw Request

- We have to implement Dialog ESMS package to send sms from the application.
- The package home page is: https://packagist.org/packages/erbitron/dialog-esms
- Composer install: composer require erbitron/dialog-esms
- We have to send email and sms notifications to the necessary users.
- When order placed, user should notify by email and a sms.
- When other required status changes of the order occured, need to send notifications. Not the all status changes, you can deside which status should be notified for the user.

## Objective

Add Dialog ESMS as the SMS delivery integration and wire order notifications so customers receive both email and SMS for the important order lifecycle events. The implementation should send a notification when an order is placed and should define a deliberate, customer-facing status notification matrix rather than notifying on every internal status transition.

## Acceptance Criteria

1. `erbitron/dialog-esms` is installed and configured in the application.
2. Placing an order sends both an email notification and an SMS notification to the customer.
3. Only the chosen customer-facing order status changes trigger notifications, and the selected set is documented in the task implementation.
4. Notification content includes the correct order reference and other actionable details for the customer.
5. The implementation does not break existing mail or order placement behavior.
6. Focused tests cover the order placed flow and at least one selected status transition with mail and SMS fakes.

## Relevant Knowledge To Read

- `.ai/knowledge/core/guardrails.md`
- `.ai/knowledge/core/best-practices.md`
- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Likely Implementation Areas

- `composer.json`
- `config/`
- `app/Notifications/`
- `app/Listeners/`
- `app/Events/`
- `app/Models/Order.php`
- `app/Actions/Order/`
- `tests/Feature/`

## Risks Or Open Questions

- Production SMS delivery requires valid Dialog ESMS credentials in `DIALOG_ESMS_API_KEY`.
- `DIALOG_ESMS_SOURCE_ADDRESS` can be set to control the SMS sender/source address.

## Test Plan

- Add or update focused feature tests for order placement notifications.
- Add or update focused feature tests for one selected order status change notification.
- Run the smallest relevant Pest suite with `php artisan test --compact`.

## Documentation Updates Required

- `.ai/knowledge/core/order-process.md`
- `.ai/knowledge/core/implementation-status.md`

## Completion Notes

- Implemented on 2026-07-02.
- Installed `erbitron/dialog-esms` and published `config/dialog-esms.php`.
- Added a custom Laravel notification channel for Dialog ESMS and a shared customer order notification.
- Order placed notifications now send by email and SMS when recipient details are available.
- Customer-facing status notifications are limited to order confirmed, order cancelled, shipment dispatched, and shipment delivered.
- Added focused Pest coverage for order placement notifications, selected status notifications, and internal status suppression.
