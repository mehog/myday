# Nuptoria Brand Colors (CSS Variables)

Gold (`#c9a227`) is the shared brand accent across surfaces. Marketing is a **light wedding** palette; Filament and the default invitation theme (`amber-gold`) keep the Night background.

## Marketing palette (landing, referral, legal, onboarding)

Scoped on `.landing-page` in `resources/css/app.css`:

```css
.landing-page {
  --landing-bg: #ffffff;              /* page background */
  --landing-bg-soft: #fafaf8;         /* section tint / soft surfaces */
  --landing-text: #1a1208;            /* headings, structure text */
  --landing-text-muted: #5c5246;      /* body / secondary */
  --landing-accent: #c9a227;          /* labels, CTAs, links */
  --landing-accent-hover: #a8841a;    /* hover gold */
  --landing-border: color-mix(in srgb, #1a1208 12%, transparent);
}
```

| Role | Hex | Use |
|---|---|---|
| Page background | `#ffffff` | Marketing page bg |
| Soft surfaces | `#fafaf8` | Alternating section tint |
| Headings / structure | `#1a1208` | H1, H2, nav brand, pricing amounts |
| Body / secondary | `#5c5246` | Paragraphs, captions, form labels |
| Accent | `#c9a227` | Eyebrows, step numbers, links, primary CTAs |
| Accent hover | `#a8841a` | Button / link hover |

Typography rules: black for structure; gold for short high-signal text only (not long body copy).

## App / invitation night palette (Amber Gold)

Used by Filament and invitation theme `amber-gold` — **not** the marketing surface identity.

```css
:root {
  /* Brand / primary */
  --color-primary: #c9a227;        /* gold — buttons, links, accents */
  --color-primary-dark: #a8841a;   /* darker gold — hover states */
  --color-accent: #f5e6c8;         /* light gold/cream accent */

  /* Backgrounds */
  --color-bg: #1a1208;             /* main dark brown background */
  --color-bg-soft: #2a1f0f;        /* elevated surfaces, cards, inputs */

  /* Text */
  --color-text: #faf6ee;           /* primary text (off-white) */
  --color-text-muted: #d4c4a8;     /* secondary/muted text (warm tan) */

  /* Hero gradient */
  --gradient-hero: linear-gradient(180deg, rgba(26,18,8,0.3) 0%, rgba(26,18,8,0.95) 100%);
}
```

## Quick reference (invitation / Filament tokens)

| Variable | Hex | Role |
|---|---|---|
| `--color-primary` | `#c9a227` | Primary brand gold (CTAs, focus rings, links) |
| `--color-primary-dark` | `#a8841a` | Hover/active gold |
| `--color-accent` | `#f5e6c8` | Light accent |
| `--color-bg` | `#1a1208` | Page background (invites / Filament night) |
| `--color-bg-soft` | `#2a1f0f` | Cards, inputs, panels |
| `--color-text` | `#faf6ee` | Body text on dark |
| `--color-text-muted` | `#d4c4a8` | Labels, placeholders, secondary copy |

## Invitation theme variants

These use the same CSS variable names and are defined in `resources/views/components/theme.blade.php`.

### Royal Wedding

```css
--color-primary: #1e3a5f;
--color-primary-dark: #152a45;
--color-accent: #d4af37;
--color-bg: #0f1a2e;
--color-bg-soft: #1a2744;
--color-text: #f8f6f0;
--color-text-muted: #b8c5d6;
--gradient-hero: linear-gradient(180deg, rgba(15,26,46,0.3) 0%, rgba(15,26,46,0.95) 100%);
```

### Lavender Dream

```css
--color-primary: #9b7bb8;
--color-primary-dark: #7d5f9a;
--color-accent: #e8dff5;
--color-bg: #2d2438;
--color-bg-soft: #3d3249;
--color-text: #faf8fc;
--color-text-muted: #c9bdd8;
--gradient-hero: linear-gradient(180deg, rgba(45,36,56,0.3) 0%, rgba(45,36,56,0.95) 100%);
```

### Winter Magic

```css
--color-primary: #7eb8da;
--color-primary-dark: #5a9bc4;
--color-accent: #e8f4fc;
--color-bg: #1a2332;
--color-bg-soft: #243044;
--color-text: #f0f8ff;
--color-text-muted: #a8c4d8;
--gradient-hero: linear-gradient(180deg, rgba(26,35,50,0.3) 0%, rgba(26,35,50,0.95) 100%);
```

### Pearl White

```css
--color-primary: #8C7355;
--color-primary-dark: #6E5A42;
--color-accent: #E8E0D5;
--color-bg: #FAFAF8;
--color-bg-soft: #F0EDE8;
--color-text: #1C1917;
--color-text-muted: #78716C;
--gradient-hero: linear-gradient(180deg, rgba(250,250,248,0.3) 0%, rgba(250,250,248,0.92) 100%);
```

### Dusty Rose

```css
--color-primary: #B5706A;
--color-primary-dark: #9A5A55;
--color-accent: #E8C9C4;
--color-bg: #F9F1EE;
--color-bg-soft: #F2E6E1;
--color-text: #2D1B16;
--color-text-muted: #8B6E65;
--gradient-hero: linear-gradient(180deg, rgba(249,241,238,0.3) 0%, rgba(249,241,238,0.92) 100%);
```

### Paper & Ink

```css
--color-primary: #9A7B4F;
--color-primary-dark: #7A623E;
--color-accent: #C4B59A;
--color-bg: #F3EDE3;
--color-bg-soft: #EBE3D6;
--color-text: #3A2E24;
--color-text-muted: #7A6B5A;
--gradient-hero: linear-gradient(180deg, rgba(243,237,227,0.25) 0%, rgba(243,237,227,0.94) 100%);
```

## Source files

- `resources/css/app.css` — marketing `.landing-page` CSS variables and landing component styles
- `resources/views/components/theme.blade.php` — invitation theme CSS variable definitions
- `resources/css/filament/app/theme.css` — Filament admin auth UI
- `app/Providers/Filament/AppPanelProvider.php` — Filament primary color (`#c9a227`)
