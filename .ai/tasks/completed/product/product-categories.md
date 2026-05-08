# Task: Product Categories

## Goal
Implement a category system for products (e.g., Pillow Covers, Cushion Covers, Wall Hangers), including admin CRUD and product assignment.

## Why
- Improves browseability and navigation.
- Enables category filters and category-based merchandising.

## Scope
1. Add product categories dictionary.
2. Assign categories to products.
3. Add admin CRUD for categories.
4. Expose categories in product list/detail and search filters.

## Data Model
1. `product_categories`
- `id`
- `name` (unique)
- `slug` (unique)
- `description` (nullable)
- `is_active` (bool, default true)
- `sort_order` (int, default 0)
- timestamps

2. Category assignment strategy
- Confirmed: many-to-many (`category_product`).

3. `category_product`
- `id`
- `product_id` (FK)
- `product_category_id` (FK)
- timestamps
- unique index on (`product_id`, `product_category_id`)

## Backend Work
1. Models & relations
- Product <-> ProductCategory relation(s).

2. Validation
- Product create/update accepts valid active category IDs.

3. Admin CRUD
- Category list/create/edit/archive.
- Slug generation and uniqueness handling.

4. Query updates
- Product index/search filtering by category.

## Frontend Work
1. Product create/edit
- Category picker UI.

2. Product listing/search
- Category filter control and active filter state.

3. Product cards/detail
- Render category labels.

## UX Rules
- Category names should be short, shopper-friendly.
- Keep category ordering stable via `sort_order`.

## Testing
1. Feature tests
- Admin category CRUD.
- Product create/update with categories.
- Category filter returns expected products.

2. Regression
- Existing product flows remain stable for products with one or multiple categories.

## Acceptance Criteria
1. Categories are manageable via admin.
2. Products can be assigned categories.
3. Customers can filter by category.
4. Category labels appear in storefront UI.
