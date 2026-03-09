# Task: Product Catalog JSON Generation from Uploaded Images

## Goal
Generate a structured JSON catalog from product images uploaded into `.ai/resources/<category-folder>/`, using filename-based product codes and inferred metadata.

## Confirmed Inputs from User
1. Images will be uploaded in category folders like:
- `.ai/resources/wall-hangers/`
- `.ai/resources/cushion-cover/`

2. Image file names represent product code.

## Required Output
Generate: `.ai/resources/products.generated.json`
Validation schema: `.ai/resources/products.generated.schema.json`

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
- Default category comes from folder name under `.ai/resources/`.
- Additional categories may be added based on product classification rules.

2. Code resolution
- `code` = filename without extension.

3. Color resolution
- Infer dominant standard colors from image.
- Classify inferred colors into primary, secondary, and tertiary roles.
- Map to project color dictionary (e.g., Black, White, Gray, Brown, Beige, Red, Blue, Green, Yellow, Gold).
- Use product image folders below as reference sources for color inference quality:
  - `.ai/resources/cushion-cover/`
  - `.ai/resources/wall-hangers/`
- Add new standardized colors only when genuinely necessary.
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
6. Validate generated JSON against `.ai/resources/products.generated.schema.json` before import.

## Follow-up Integration
1. Use generated JSON to seed/import products and attach:
- categories (many-to-many)
- colors (many-to-many)

2. Log unmatched/ambiguous items for manual review.

## Acceptance Criteria
1. JSON file is generated successfully from uploaded folders.
2. All entries include `name, code, description, colors, categories`.
3. Duplicate codes and invalid entries are reported.
4. Output is ready for product import pipeline.
