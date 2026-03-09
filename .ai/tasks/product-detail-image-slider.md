# Task: Product Detail Image Slider

## Goal
Improve the product detail page image experience by introducing a gallery slider with touch swipe support on mobile devices.

## Why
- Better product visualization when a listing has multiple images.
- Mobile-first interaction for natural browsing with finger swipe.
- Improves usability and engagement on product detail pages.

## Scope
1. Show slider/carousel UI on product detail page when product has more than one image.
2. Keep single-image layout simple when only one image exists.
3. Enable swipe gesture support on mobile.
4. Preserve desktop usability with hover-revealed navigation controls and optional thumbnails.
5. Keep image aspect ratio preserved in slideshow rendering (no forced crop).

## Backend Work
1. Product detail payload
- Ensure product images are returned in deterministic order (`sort_order`, fallback by id).
- Include all required fields for gallery rendering (`id`, `url`, `alt_text`).

2. Media constraints
- Ensure only image media types are passed to gallery image list.

## Frontend Work
1. Slider behavior
- Add previous/next navigation controls.
- Add active image state.
- Loop or clamp behavior must be consistent and documented.

2. Mobile support
- Implement touch swipe gesture for left/right image navigation.
- Keep tap targets large enough for thumbs.

3. UX polish
- Do not show image index indicator (for example `2 / 5`) on slider.
- Add optional thumbnail strip on desktop if space allows.
- Preserve uploaded image ratio in main slide display.

4. Accessibility
- Keyboard navigation support for previous/next controls.
- Proper `aria-label` for controls.
- Respect meaningful `alt` text.

## UX Rules
- If only one image exists, hide slider controls.
- Keep original image aspect ratio in slideshow (avoid cropping).
- Desktop: show previous/next controls on image hover or focus.
- Mobile: users slide with finger swipe gestures.

## Testing
1. Feature / integration tests (where applicable)
- Product detail page renders all product images in expected order.
- Single-image product hides navigation controls.
- Multi-image product shows navigation controls.

2. Frontend behavior tests (if available)
- Next/previous navigation updates active slide correctly.
- Mobile swipe left/right changes slides correctly.
- Slider UI does not render image number indicator.

## Acceptance Criteria
1. Product detail page shows a working slider for multi-image products.
2. Mobile users can swipe images with finger gestures.
3. Single-image products render without unnecessary slider controls.
4. Gallery remains accessible and visually stable.
