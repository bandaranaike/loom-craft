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
6. When the main product image area reaches the bottom of the viewport during downward scroll, float the thumbnail strip on top of the image itself.
7. The floating thumbnail strip must stay attached to the image and render at the bottom-left edge of the image area, not fixed to the page or viewport bottom.
8. When scrolling back up and the image area no longer touches the bottom of the viewport, return the thumbnail strip to its normal in-layout position.

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
- When the image gallery reaches the viewport bottom edge, float `renderThumbnails` inside the image area.
- The floating `renderThumbnails` position must remain aligned to the bottom-left of the image.
- When the gallery moves away from the viewport bottom edge during upward scroll, restore `renderThumbnails` to its normal flow layout below the image.

4. Accessibility
- Keyboard navigation support for previous/next controls.
- Proper `aria-label` for controls.
- Respect meaningful `alt` text.

## UX Rules
- If only one image exists, hide slider controls.
- Keep original image aspect ratio in slideshow (avoid cropping).
- Desktop: show previous/next controls on image hover or focus.
- Mobile: users slide with finger swipe gestures.
- Floating thumbnail behavior should only apply while the main image/gallery area is visually touching the bottom of the viewport.
- The floating thumbnail strip must remain visually attached to the image, not the page.
- The floating thumbnail strip must appear at the bottom-left of the image while floating.
- Floating behavior must not permanently cover unrelated page content once the gallery scroll state changes back to normal.

## Testing
1. Feature / integration tests (where applicable)
- Product detail page renders all product images in expected order.
- Single-image product hides navigation controls.
- Multi-image product shows navigation controls.

2. Frontend behavior tests (if available)
- Next/previous navigation updates active slide correctly.
- Mobile swipe left/right changes slides correctly.
- Slider UI does not render image number indicator.
- Thumbnail strip floats on the image at the bottom-left when the gallery reaches the viewport bottom edge.
- Thumbnail strip returns to normal document flow when scrolling upward away from that edge.

## Acceptance Criteria
1. Product detail page shows a working slider for multi-image products.
2. Mobile users can swipe images with finger gestures.
3. Single-image products render without unnecessary slider controls.
4. Gallery remains accessible and visually stable.
5. Thumbnail strip floats on the image at the defined scroll threshold, anchored to the image bottom-left, and restores normal behavior when scrolling back up.
