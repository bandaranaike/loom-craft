# Task: Product Catalog JSON Generation from Uploaded Images

## Goal
Generate a structured JSON catalog from product images uploaded into `.ai/knowledge/assets/source-products/<category-folder>/`, using filename-based product codes and inferred metadata.

## Confirmed Inputs from User
1. Images will be uploaded in category folders like:
- `.ai/knowledge/assets/source-products/wall-hangers/`
- `.ai/knowledge/assets/source-products/cushion-cover/`

2. Image file names represent product code.

## Required Output
Generate: `.ai/knowledge/assets/generated/products.generated.json`
Validation schema: `.ai/knowledge/assets/generated/products.generated.schema.json`

Each item must include:
- `name`
- `code`
- `description`
- `colors`
- `categories`

Color role rule:
- `colors` must be ordered by role priority:
1. Primary color first (dominant visual color).
2. Secondary color(s) next (supporting but clearly visible colors).
3. Tertiary color(s) last (minor accent colors).

Example:
```json
[
  {
    "name": "Wall Hanger - Geometric Loom Pattern",
    "code": "DR-WH-01001",
    "description": "Handwoven wall hanger featuring traditional geometric motifs.",
    "colors": ["Brown", "Beige", "Black"],
    "categories": ["wall-hangers", "home-decor"]
  }
]
```

## Processing Rules
1. Category resolution
- `categories` must be an array.
- Default category comes from folder name under `.ai/knowledge/assets/source-products/`.
- Additional categories may be added based on product classification rules.

2. Code resolution
- `code` = filename without extension.

3. Color resolution
- Infer dominant standard colors from image.
- Classify inferred colors into primary, secondary, and tertiary roles.
- Map to the shared project color registry in `resources/data/product-colors.json`.
- Use product image folders below as reference sources for color inference quality:
  - `.ai/knowledge/assets/source-products/cushion-cover/`
  - `.ai/knowledge/assets/source-products/wall-hangers/`
- Add new standardized colors only when genuinely necessary.
- When a genuinely new standardized color is required, update `resources/data/product-colors.json` in the same task run.
- Each new color added to `resources/data/product-colors.json` must include:
  - `name`
  - `slug`
  - `hex`
- Preserve deterministic ordering in `resources/data/product-colors.json` so downstream seeding and frontend swatches remain stable.
- Keep `colors` array role-ordered: primary first, then secondary, then tertiary.

4. Name/description generation
- Generate concise shopper-friendly values from visual cues and category context.
- Keep tone consistent with storefront style.

5. Idempotency
- Regeneration should produce stable entries for unchanged files.
- Keep deterministic ordering: primary folder category, then code.

## Validation
1. Ensure every JSON object has all required fields.
2. Ensure `colors` is a non-empty array.
3. Ensure `colors` is role-ordered (primary → secondary → tertiary).
4. Ensure `categories` is a non-empty array and each value matches known/created categories.
5. Reject duplicate `code` values.
6. Validate generated JSON against `.ai/knowledge/assets/generated/products.generated.schema.json` before import.

## Follow-up Integration
1. Use generated JSON to seed/import products and attach:
- categories (many-to-many)
- colors (many-to-many)

2. Treat `resources/data/product-colors.json` as the single source of truth for available product colors.

3. If new colors were added to `resources/data/product-colors.json`, ensure the updated catalog uses those exact color names consistently.

4. Log unmatched/ambiguous items for manual review.

## Acceptance Criteria
1. JSON file is generated successfully from uploaded folders.
2. All entries include `name, code, description, colors, categories`.
3. Duplicate codes and invalid entries are reported.
4. Any newly discovered standardized colors are added to `resources/data/product-colors.json`.
5. Output is ready for product import pipeline.
