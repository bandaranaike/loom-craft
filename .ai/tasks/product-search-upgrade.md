# Task: Product Search Upgrade

## Goal
Upgrade product search to support multi-filter discovery by color, price, vendor/brand, name, and category, with a 600ms input debounce.

## Why
- Reduces friction in product discovery.
- Improves search relevance and conversion.

## Scope
1. Add filters:
- Text/name
- Vendor/brand
- Category
- Color
- Price range (min/max)

2. Add 600ms debounce for text search input.
3. Keep URL query synchronized for shareable/searchable links.
4. Preserve pagination + filter state.

## Backend Work
1. Product index query composition
- Apply filters only when provided.
- Keep approved/active visibility constraints.

2. Performance
- Add DB indexes for commonly filtered columns.
- Eager load required relations.

3. Request validation
- Sanitize and validate filter params.

## Frontend Work
1. Filter panel/UI
- Filter chips/selects/sliders for category, color, vendor, price.
- Clear-all and per-filter removal.

2. Debounce behavior
- 600ms debounce on text input before request.

3. State UX
- Loading state while filter query updates.
- Empty state with a quick reset action.

## UX Rules
- Keep filters visible and understandable.
- Mobile: collapsible filter panel/drawer.
- Desktop: inline filter controls.

## Testing
1. Feature tests
- Filtering by each parameter independently.
- Combined filters return correct intersections.
- Pagination remains correct with filters.

2. Frontend behavior tests (if available)
- Debounce delay respected.
- URL/query sync correctness.

## Acceptance Criteria
1. Users can search/filter by all requested dimensions.
2. Text search triggers after 600ms pause.
3. Filters are reflected in URL and persist through navigation.
4. Search remains performant on realistic dataset sizes.
