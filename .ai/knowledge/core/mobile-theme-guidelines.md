# LoomCraft Shared Theme Guidelines

This file is for the Android mobile development agent building the admin/vendor order-management app in Kotlin.

Goal:
- Keep the Android app visually aligned with the existing LoomCraft website.
- Reuse the same brand mood, color roles, spacing feel, and component language.
- Do not invent a second design system.

Source of truth on web:
- `resources/css/app.css`
- `resources/js/pages/welcome.tsx`
- `resources/js/pages/vendor/products/create.tsx`
- `resources/js/pages/vendor/products/edit.tsx`
- `resources/js/pages/vendor/products/index.tsx`

---

## 1. Brand Direction

LoomCraft is not a generic SaaS dashboard. The visual language is:

- warm
- tactile
- heritage-focused
- editorial rather than corporate
- premium, restrained, and earthy

Avoid:

- neon colors
- blue-heavy enterprise UI
- pure black and pure white as dominant surfaces
- overly sharp corners
- glossy gradients
- loud Material default palettes

The app should feel like a refined craft brand with operational tooling layered on top.

---

## 2. Theme Structure

There are effectively two related color layers on the website:

1. Base application semantic tokens
- Used by app shell, dashboard, sidebar, controls, and common UI primitives.
- Defined in `resources/css/app.css` as `--background`, `--foreground`, `--primary`, `--accent`, `--border`, etc.

2. Brand/editorial "welcome" tokens
- Used heavily across public pages, auth pages, and many vendor-facing forms.
- Defined as `--welcome-*` variables in `resources/css/app.css`.
- These are the clearest source for exact brand color values in hex.

For the Android admin/vendor app:

- Use the brand/editorial palette as the visible identity layer.
- Use semantic roles like `primary`, `surface`, `border`, `text`, `error`, `accent` in code.
- Keep the naming semantic even if the actual values come from the `--welcome-*` palette.

---

## 3. Canonical Light Palette

Use these as the default light theme values.

### Brand core

- `BrandStrong` = `#2B241C`
- `BrandStrongHover` = `#3A2F25`
- `BrandBodyText` = `#5A4A3A`
- `BrandMutedText` = `#7A5A3A`
- `BrandMutedStrong` = `#6B4F34`
- `BrandAccent` = `#B6623A`
- `BrandDanger` = `#A35237`

### Surfaces

- `SurfaceBase` = `#FDF8F0`
- `SurfaceRaised` = `#FFF8ED`
- `SurfaceTint` = `#F9EFE2`

### Borders

- `BorderDefault` = `#D4B28C`
- `BorderSoft` = `#E0C7A7`
- `BorderAlt` = `#B98A5F`
- `BorderQuiet` = `#B3A08D`

### Text on dark/strong surfaces

- `OnStrong` = `#F6F1E8`
- `OnStrongMuted` = `#D5CCC0`

### Supporting accent

- `AccentSoft` = `#E6C9A6`

Recommended semantic mapping:

- `primary` -> `BrandStrong`
- `onPrimary` -> `OnStrong`
- `secondaryContainer` -> `SurfaceTint`
- `surface` -> `SurfaceBase`
- `surfaceVariant` -> `SurfaceRaised`
- `outline` -> `BorderDefault`
- `outlineVariant` -> `BorderSoft`
- `tertiary` or `accent` -> `BrandAccent`
- `error` -> `BrandDanger`
- `onSurface` -> `BrandStrong`
- `onSurfaceVariant` -> `BrandBodyText`

---

## 4. Canonical Dark Palette

Use these if the app supports dark mode.

### Brand core

- `BrandStrongDark` = `#D9C0A3`
- `BrandStrongHoverDark` = `#EAD2B8`
- `BrandBodyTextDark` = `#DAC2A7`
- `BrandMutedTextDark` = `#BFA386`
- `BrandMutedStrongDark` = `#A98F74`
- `BrandAccentDark` = `#D0895F`
- `BrandDangerDark` = `#CD7F61`

### Surfaces

- `SurfaceBaseDark` = `#241D17`
- `SurfaceRaisedDark` = `#201912`
- `SurfaceTintDark` = `#2C221A`

### Borders

- `BorderDefaultDark` = `#6F5A49`
- `BorderSoftDark` = `#5B493A`
- `BorderAltDark` = `#8A725F`
- `BorderQuietDark` = `#7C6858`

### Text on strong surfaces

- `OnStrongDark` = `#0B0806`
- `OnStrongMutedDark` = `#3D3126`

### Supporting accent

- `AccentSoftDark` = `#B99575`

Recommended semantic mapping:

- `primary` -> `BrandStrongDark`
- `onPrimary` -> `OnStrongDark`
- `surface` -> `SurfaceBaseDark`
- `surfaceVariant` -> `SurfaceRaisedDark`
- `secondaryContainer` -> `SurfaceTintDark`
- `outline` -> `BorderDefaultDark`
- `outlineVariant` -> `BorderSoftDark`
- `tertiary` or `accent` -> `BrandAccentDark`
- `error` -> `BrandDangerDark`
- `onSurface` -> `BrandStrongDark`
- `onSurfaceVariant` -> `BrandBodyTextDark`

---

## 5. Usage Rules

### Primary action

Use:

- filled button in `BrandStrong`
- text/icon in `OnStrong`
- hover/pressed state should move toward `BrandStrongHover`

Do not use bright blue or green for primary actions.

### Secondary action

Use:

- outline button
- border in `BrandStrong` or `BorderDefault`
- text in `BrandStrong`
- pressed background can use `SurfaceTint`

### Accent/highlight

Use `BrandAccent` sparingly for:

- active chips
- important badges
- progress accents
- selected state emphasis

Do not make the whole app orange. Accent is for emphasis, not base fill.

### Error/destructive

Use `BrandDanger` / `BrandDangerDark`.

### Backgrounds

Default app screens should use:

- `SurfaceBase` as page background
- `SurfaceRaised` for cards, sheets, grouped containers
- `SurfaceTint` for subtle highlighted panels

Avoid flat pure white cards unless required by platform components.

---

## 6. Typography

### Primary typeface

Website base font:

- `Instrument Sans`

Android guidance:

- If possible, use `Instrument Sans` as the main UI font.
- If that is not available, use a clean sans-serif fallback with similar proportions.

### Editorial/display type

The website uses `Playfair Display` in public-facing marketing pages.

For the admin/vendor mobile app:

- Do not use serif display type for dense operational screens.
- It may be used sparingly in splash, onboarding, or brand headers.
- Main app navigation, tables, forms, and order workflows should stay sans-serif.

### Text behavior

Match the website tone:

- high-contrast titles
- softer brown secondary text
- uppercase micro-labels with generous letter spacing for metadata

Recommended mobile pattern:

- screen title: semibold/bold sans
- section eyebrow: uppercase, small size, wider tracking
- body copy: regular weight with warm muted brown, not gray-blue

---

## 7. Shape Language

The web UI uses soft, generous rounding.

Observed patterns:

- base radius token is about `10px`
- many cards/forms use `20px`, `24px`, `28px`, `36px`, `40px`
- many action buttons are pill-shaped

Android guidance:

- primary buttons: pill or near-pill
- cards: 20dp to 28dp corner radius
- modal sheets/panels: 24dp to 32dp
- inputs: 20dp to 24dp
- chips/filter pills: fully rounded

Avoid tiny 4dp rectangles. The current LoomCraft feel is soft and crafted.

---

## 8. Elevation and Borders

The website relies more on:

- warm borders
- soft shadows
- layered surfaces

than on hard elevation.

Android guidance:

- prefer bordered cards plus subtle elevation
- use warm shadow values with low opacity
- do not rely on harsh black drop shadows
- use borders to separate grouped content before increasing elevation

Visual priority order:

1. surface contrast
2. border
3. subtle shadow

---

## 9. Component Guidance

### App bar / top bar

- background: `SurfaceBase` or `SurfaceTint`
- title: `BrandStrong`
- icons: `BrandStrong`
- optional bottom divider: `BorderSoft`

### Bottom navigation / navigation rail / drawer

- selected item: `BrandStrong` text/icon on a lightly tinted background
- unselected item: `BrandMutedText`
- container surface: `SurfaceTint` or `SurfaceBase`

### Cards

- background: `SurfaceRaised`
- border: `BorderSoft`
- title: `BrandStrong`
- supporting text: `BrandBodyText`

### Form fields

- background: `SurfaceBase` or `SurfaceTint`
- border: `BorderDefault`
- text: `BrandStrong`
- placeholder: muted brown, not neutral gray-blue
- focused border/ring: `BrandStrong`

### Status chips

Use restrained, readable variants:

- pending: `AccentSoft` background with `BrandStrong` text
- processing: lightly tinted accent or surface with `BrandAccent` text
- shipped: muted strong text on soft tinted background
- delivered: use a calm success color only if needed, but keep it warm and desaturated
- cancelled/disputed: use `BrandDanger`

Do not introduce saturated default Material status colors without checking fit.

---

## 10. Imagery and Illustration Direction

When the mobile app uses banners, empty states, or supporting graphics:

- prefer woven texture, craft detail, and earthy warmth
- use cream, clay, brown, muted gold, and soft terracotta
- avoid tech-style abstract gradients and futuristic glassmorphism

---

## 11. Motion

Motion should feel calm and precise.

Use:

- short fades
- subtle vertical movement
- restrained scale on tap

Avoid:

- springy overshoot everywhere
- flashy parallax
- playful bounce effects in admin/vendor workflows

---

## 12. Implementation Advice For Kotlin

If using Jetpack Compose:

- define a `LoomCraftColorScheme`
- keep semantic names in code: `Primary`, `Surface`, `SurfaceVariant`, `Outline`, `Accent`, `Danger`
- map those semantic tokens to the brand hex values above
- support both light and dark themes

If using XML views:

- create a central color resource file and theme overlay
- do not hardcode screen-level colors

In both cases:

- centralize radii
- centralize spacing scale
- centralize typography roles

---

## 13. Non-Negotiables

- Use the warm LoomCraft palette, not default Material blue.
- Keep buttons, cards, and inputs rounded and soft.
- Use earthy brown text instead of cool gray-blue text.
- Preserve the premium heritage tone across both website and app.
- Treat `resources/css/app.css` as the master token source if the website theme evolves.

---

## 14. Fast Start Mapping

If the mobile agent needs a minimal starting set, use this:

- background: `#FDF8F0`
- surface: `#FFF8ED`
- surfaceAlt: `#F9EFE2`
- primary: `#2B241C`
- primaryPressed: `#3A2F25`
- accent: `#B6623A`
- textPrimary: `#2B241C`
- textSecondary: `#5A4A3A`
- textMuted: `#7A5A3A`
- border: `#D4B28C`
- borderSoft: `#E0C7A7`
- onPrimary: `#F6F1E8`
- error: `#A35237`

This set is enough to build the first Android screens without drifting away from the website.
