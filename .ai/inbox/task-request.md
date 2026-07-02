# Freeform Task Request

Write the task here in your own way.

No structure is required.

Useful things to include when available:

- What you want changed
- Why it matters
- Screens, routes, or files involved
- Expected behavior
- Constraints or things to avoid
- Any examples

Latest note:

- [ ] I found some fixes required in the application
    - The order showing page (/orders/ORD-NTKK6VIMC1Z9JUQ1IONPQH9QI2AV | /orders/{orders.public_id}) giving 403 Permission Required Access Restricted warning message. Either this page should be public or it should be redirected to the login page and once logged in, need to get back to the showing page. the email should say it self as something like 'to access this link you have to login to the account' 
    - I can see order status in technical level such as `collection_pending`. Instead of showing this technical level statuses, let's introduce a label with proper icon + meaningful short status name with suitalbe background and text colours. 
    -  In the dark mode of outlook, the email is not showing properly. We need to handle by adding dark mode css. You may see the screenshot in `/var/www/loom-craft/.ai/knowledge/assets/guidelines/OutlookLoomCraftEmailDarkTheme.png`
