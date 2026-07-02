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

- [ ] We have to implement Dialog ESMS package to send sms from the application.
    - The package home page is : https://packagist.org/packages/erbitron/dialog-esms
    - Composer install : composer require erbitron/dialog-esms

- [ ] We have to send email and sms notifications to the necessary users. 
    - When order placed, user should notify by email and a sms
    - When other required status changes of the order occured, need to send notifications. Not the all status changes, you can deside which status should be notified for the user.

- [ ] The user vissible order number should be `odrders.order_number` column. the public id should use only for urls. 

- [ ] The 'Doucmentation' and the 'Repository' links of the logged in side menu is not required. 
