# Task: Product Colors

## Goal
Introduce standardized product colors so customers can discover products visually, and admins can manage the available color set.

## Why
- Improves product discovery and conversion for visual shoppers.
- Creates a reusable taxonomy for filtering and product metadata.

## Scope
1. Create a standard color dictionary (not exact hex matching).
2. Assign one or more colors to each product.
3. Display product colors in search/listing and product detail pages.
4. Allow vendors/admin to assign colors during create/edit product flows.
5. Build admin CRUD for product colors.

## Data Model
1. `product_colors`
- `id`
- `name` (unique)
- `slug` (unique)
- `is_active` (bool, default true)
- `sort_order` (int, default 0)
- timestamps

2. `product_color_product` pivot
- `id`
- `product_id` (FK, cascade delete)
- `product_color_id` (FK, cascade delete)
- timestamps
- unique index on (`product_id`, `product_color_id`)

## Backend Work
1. Models & relations
- Product `belongsToMany(ProductColor::class)`
- ProductColor `belongsToMany(Product::class)`

2. Product create/update validation
- `color_ids` array of existing active colors.
- Sync pivot on create/update.

3. Product search API/query updates
- Add color filter support by `slug` or `id`.

4. Admin CRUD
- Index/create/update/delete for color dictionary.
- Prevent deleting colors used by products (or soft-disable via `is_active`).

5. Image-assisted workflow (confirmed)
- User uploads product images into category folders under `.ai/knowledge/assets/source-products/` (e.g., `.ai/knowledge/assets/source-products/wall-hangers`, `.ai/knowledge/assets/source-products/cushion-cover`).
- Product code is derived from image file name.
- Generate JSON catalog entries with: `name`, `code`, `description`, `colors`, `categories`.
- If a detected color is new, add it to `product_colors` dictionary before linking.

## JSON Generation Spec
1. Input
- Folder structure under `.ai/knowledge/assets/source-products/<category-slug>/`.
- Image filename base is product code (example: `DR-WH-01001.jpg` -> `code: DR-WH-01001`).

2. Output
- Generated file path: `.ai/knowledge/assets/generated/products.generated.json`.
- Shape:
```json
[
  {
    "name": "Wall Hanger - Heritage Pattern",
    "code": "DR-WH-01001",
    "description": "Handloom wall hanger with traditional Dumbara motif.",
    "colors": ["Beige", "Brown", "Black"],
    "categories": ["wall-hangers"]
  }
]
```

3. Rules
- `categories` is an array. Add the folder name as a default category and append additional categories when applicable.
- `colors` must use standardized names (no hex).
- `name` and `description` can be generated from product code + visual cues and later manually refined.

## Frontend Work
1. Product listing/search
- Render color boxes/swatches on product cards (instead of color names) in `/products`.
- Render same color boxes/swatches on home page product cards.
- Add color filter UI (multi-select chips/checks).

2. Product detail
- Render assigned colors as color boxes/swatches near metadata.

3. Product create/edit
- Multi-select colors from standard color list.

4. Admin pages
- Color list + form for CRUD.

## UX Rules
- Keep color names simple and standard (e.g., Red, Blue, Green, Brown, Beige, Black, White, Gold).
- Use consistent color box/swatch style and contrast across `/products`, home page, and product detail.
- Avoid exposing technical color codes in customer UI.

## Component Reuse
- Prefer a shared frontend component for color swatch rendering so product cards and product detail stay visually consistent.

## Testing
1. Feature tests
- Admin can CRUD product colors.
- Vendor/admin can attach/sync product colors.
- Product search filters by color correctly.

2. Query/serialization tests
- Product card and product show payloads include colors.

## Acceptance Criteria
1. Colors can be created and managed in admin.
2. Products can have multiple colors.
3. Search can filter by selected colors.
4. Product list/detail pages show assigned colors.
5. No N+1 regressions in product listing/search.
