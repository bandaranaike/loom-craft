# Task: Application Currencies

## Goal
Keep product pricing canonical in LKR, while allowing PayPal checkout by converting the payable amount into a PayPal-supported currency and preserving the exact exchange-rate snapshot used for the transaction.

## Current Context
- Product prices are currently stored without an explicit currency column and appear to be treated as LKR by default through `products.selling_price`.
- The application currently allows `USD`, `EUR`, and `LKR` in cart and checkout validation.
- Orders and payments currently persist a single `currency` and amount, but do not store exchange-rate metadata.
- PayPal order creation currently sends the checkout currency directly to PayPal with no conversion layer.
- PayPal does not support LKR, so the current flow is incomplete if the checkout currency is LKR.

## Problem To Solve
1. Products should continue to have one canonical price source.
2. PayPal checkout must not rely on LKR being accepted.
3. The customer must see the conversion before leaving for PayPal.
4. The system must persist the exact conversion inputs used for the final PayPal charge.
5. Exchange rates must be refreshed automatically instead of being fetched ad hoc during checkout.

## Recommended Product Direction
1. Treat LKR as the canonical application pricing currency for products.
2. Introduce a dedicated exchange-rate store and a conversion service.
3. For PayPal, convert LKR into one supported settlement currency before creating the PayPal order.
4. Use `USD` as the only PayPal settlement currency for this task.
5. Show the converted amount as approximate until the customer confirms the PayPal checkout step.
6. Persist both the original LKR amount and the actual converted amount/rate used at the moment the PayPal order is created.
7. Use pure market-rate conversion with no markup or protective buffer.
8. Keep exchange-rate history instead of overwriting older snapshots.

## Scope
### In Scope
1. Define canonical currency behavior for products and checkout.
2. Add exchange-rate storage and refresh mechanics.
3. Add a backend conversion path for PayPal checkout.
4. Add customer-facing messaging and confirmation before redirecting to PayPal.
5. Persist historical exchange-rate data needed for audit and support.
6. Add automated tests for conversion, checkout validation, and persistence.

### Out Of Scope For This Task
1. General multi-currency pricing across the whole storefront.
2. Customer-visible currency switching in cart and checkout.
3. Letting vendors manage different base currencies per product.
4. Real-time rate fetching during every checkout request.

## Target Behavior
1. Product prices remain stored and managed in LKR.
2. Cart and checkout totals are calculated from the LKR canonical amount.
3. When the customer selects PayPal, the system loads the latest stored `LKR -> USD` rate.
4. The checkout page shows:
   - original LKR amount
   - approximate USD amount
   - exchange rate used
   - clear message that PayPal wallet and PayPal card payments are both charged in the supported foreign currency
5. The customer must explicitly confirm this conversion before the PayPal order is created.
6. The PayPal order is created using the converted amount, rounded to 2 decimal places, for both the redirect-based wallet flow and the on-page card flow.
7. After approval/capture, the final order/payment records retain both LKR and converted payment details.
8. Non-PayPal checkout paths continue to operate with LKR as the application checkout currency after the simplification.

## Data Model Requirements
### Canonical Pricing
1. Keep product selling price canonical in LKR.
2. Do not duplicate product prices into multiple currencies.
3. If the codebase needs explicit clarity, add configuration for application base currency rather than adding per-product currency unless there is a real business requirement.

### Exchange Rates
1. Create an `exchange_rates` table.
2. Minimum columns:
   - `from_currency`
   - `to_currency`
   - `rate`
   - `source`
   - `fetched_at`
   - timestamps
3. Add a uniqueness rule for one active latest row per currency pair strategy, or define how history is retained if multiple snapshots are stored.
4. Recommended approach:
   - keep history rows
   - query the latest row per currency pair for live usage
5. Initial required currency pair is `LKR -> USD`.

### Order / Payment Snapshot Data
1. Persist the original LKR amount used for checkout.
2. Persist the converted currency code used for PayPal, likely `USD`.
3. Persist the converted amount sent to PayPal.
4. Persist the exchange rate used.
5. Persist the rate source used.
6. Persist the provider reference already returned by PayPal.
7. Decide whether these fields belong on `payments`, `orders`, or both, but avoid storing them only in session.

## Backend Work
### Currency Domain
1. Revisit the current `Currency` value object and checkout validation rules.
2. Simplify the application away from customer-visible `USD` / `EUR` / `LKR` selection in cart and checkout.
3. Treat LKR as the checkout currency inside the app, while using USD only as the PayPal settlement currency.
4. Avoid mixing display currency selection with settlement currency behavior.

### Exchange Rate Service
1. Introduce a dedicated service for currency conversion.
2. It should:
   - read the latest stored rate
   - convert LKR into USD for PayPal
   - round the payable amount to two decimals
   - not apply any markup or buffer
3. Keep the source API integration behind this service so the rest of the app does not depend on vendor-specific payloads.

### Scheduled Rate Refresh
1. Add a scheduled command/job to refresh required currency pairs.
2. Initial required pair is `LKR -> USD`.
3. A rate older than 24 hours must be treated as stale and must block PayPal checkout.
4. Handle stale-rate scenarios explicitly:
   - reject PayPal checkout if no recent rate exists
   - reject PayPal checkout if the newest stored rate is older than 24 hours

### PayPal Checkout Flow
1. Update PayPal checkout creation so it does not pass through LKR directly.
2. Before creating the PayPal order:
   - resolve the checkout subtotal in LKR
   - fetch the latest exchange rate snapshot
   - compute the converted payable amount
   - validate that the customer confirmed the conversion details
3. Create the PayPal order using the supported foreign currency amount.
4. Store the conversion snapshot in the pending PayPal session payload so approval/capture uses the same values.
5. On final order placement, persist that same snapshot to the database.
6. Support two PayPal execution paths:
   - wallet checkout by redirecting to PayPal approval
   - direct card checkout using PayPal Card Fields and server-side capture

### Persistence Integrity
1. Historical orders must not depend on future exchange-rate changes.
2. Never recalculate old PayPal orders from the latest live rate.
3. The stored order/payment snapshot must be enough to explain:
   - original LKR subtotal
   - charged foreign subtotal
   - rate used
   - payment provider reference

## Frontend Work
1. Update the checkout page only where a PayPal option is selected.
2. Add a conversion summary panel that shows:
   - `Total: LKR ...`
   - `Approx. USD ...`
   - `Rate: 1 USD = ... LKR`
3. Add a short trust message explaining that LKR is converted automatically because PayPal does not support LKR.
4. Add a required confirmation control before either PayPal action is allowed.
5. For PayPal wallet, continue redirecting to PayPal after creating the converted order.
6. For PayPal card, render PayPal Card Fields on the checkout page and capture the approved order without leaving the site.
7. Keep the UI clear that the amount is approximate until the PayPal order is created with the stored rate snapshot.
8. If rate data is unavailable or stale, disable both PayPal wallet and PayPal card flows and show a useful message.
9. Remove or adjust any cart/checkout currency picker so the simplified LKR-first flow is clear.

## API / Provider Choice
1. Use one free exchange-rate provider behind an internal abstraction.
2. Selection criteria:
   - stable free tier
   - simple HTTP integration
   - clear usage limits
   - reliable LKR coverage
3. Do not spread provider logic across controllers/actions.
4. Store the chosen provider name in the `source` column for traceability.

## Confirmed Decisions
1. PayPal settlement currency is `USD` only.
2. The app should be simplified so customers are no longer choosing among `USD`, `EUR`, and `LKR` during cart or checkout.
3. Conversion uses pure market rate with no markup, and any loss is absorbed by the business.
4. Exchange rates older than 24 hours are stale and must block PayPal checkout.
5. Exchange-rate history must be preserved.

## Suggested Execution Order
1. Add exchange-rate schema and model.
2. Add rate-fetch service plus scheduled refresh command/job.
3. Add conversion service and stale-rate rules.
4. Extend PayPal checkout flow to use conversion snapshot data.
5. Extend order/payment persistence for exchange-rate audit fields.
6. Update checkout UI with PayPal conversion summary, confirmation, and PayPal Card Fields support.
7. Simplify cart and checkout away from customer-selected currencies.
8. Add or update feature tests for wallet checkout, card checkout, and persistence.

## Tests
1. Exchange-rate refresh stores the latest rate correctly.
2. Conversion service converts LKR to PayPal currency and rounds to two decimals.
3. PayPal checkout rejects direct LKR settlement and uses converted currency instead.
4. PayPal checkout fails gracefully when no valid exchange rate exists.
5. PayPal checkout fails gracefully when the latest rate is older than 24 hours.
6. Customer confirmation is required before PayPal order creation.
7. Pending PayPal session data stores the conversion snapshot.
8. Approved PayPal wallet checkout persists:
   - original LKR amount
   - converted amount
   - converted currency `USD`
   - rate used
   - source
9. Approved PayPal card checkout persists:
   - original LKR amount
   - converted amount
   - converted currency `USD`
   - rate used
   - source
10. Historical order/payment data remains unchanged even after rates are refreshed later.
11. Cart and checkout no longer expose customer-selectable `USD` or `EUR` flows after the simplification.

## Acceptance Criteria
1. Product pricing remains canonical in LKR.
2. PayPal checkout never attempts to charge LKR.
3. The customer sees and confirms the conversion details before starting either PayPal checkout path.
4. The exact exchange-rate snapshot used for the PayPal charge is stored permanently.
5. Exchange rates are refreshed automatically on a schedule.
6. PayPal is blocked when rate data is missing or stale.
7. Cart and checkout are simplified to the LKR-first flow for customers, with USD used only when settling PayPal.
8. Both PayPal wallet and PayPal card checkouts use the same conversion snapshot rules.
9. The flow is covered by automated tests.
