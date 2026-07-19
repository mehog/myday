# NasDan — Project Design

Product, UX, and visual design reference for the NasDan digital wedding invitation platform. For technical architecture, see [Project Context](../architecture/project-context.md).

---

## 1. Product intent

NasDan (“naš dan” — “our day”) helps couples in Bosnia and Herzegovina and the wider Balkans create a personal digital wedding invitation that guests open like a real page, not a chat photo.

The product should feel:

- **Personal** — each guest can open a link that already knows their name
- **Ceremonial** — invitation pages feel like an event artifact, not a SaaS dashboard
- **Practical** — RSVP, schedule, location, and messages reduce wedding-day chaos
- **Trustworthy** — warm, clear, multilingual; no hype language or template gimmicks

Primary locales: Bosnian (default), English, German.

---

## 2. Users and jobs

### Couple (primary customer)

| Job | Success looks like |
|-----|--------------------|
| Create an invitation | Theme, template, photos, schedule, and couple details set in one flow |
| Share with guests | Personal or public links ready for WhatsApp / Viber / Telegram |
| Track attendance | RSVP status clear in the couple panel |
| Collect messages | Text, photo, and audio messages land in one place |
| Notify guests | Push notifications reach opted-in devices |
| Refer others | Referral link and earnings are easy to find |

### Guest

| Job | Success looks like |
|-----|--------------------|
| Open invitation | Fast load, clear couple names, date, and next action |
| RSVP | One obvious path; optional +1 / note when enabled |
| Understand the day | Schedule, venue, gallery, and music without hunting |
| Leave a message | Short, friendly form; works on mobile |
| Opt into push | Permission request only on a dedicated page |

### Admin (operator)

| Job | Success looks like |
|-----|--------------------|
| Activate invitations | After off-platform payment / vetting |
| Manage users & events | Full CRUD and support visibility |
| Process referral payouts | Clear pending amounts and payout records |

---

## 3. Application surfaces

Three surfaces, three tones. Do not mix dashboard chrome into guest invitations, or marketing cards into the couple panel.

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────────┐
│  Marketing (/)  │     │  Couple (/app)   │     │  Guest (/e/…)       │
│  Brand & offer  │────▶│  Manage wedding  │────▶│  Live invitation    │
│  Dark / gold    │     │  Filament UI     │     │  Theme + template   │
└─────────────────┘     └──────────────────┘     └─────────────────────┘
```

| Surface | Route | Design role |
|---------|-------|-------------|
| Marketing | `/` | Convert couples; brand-first, human copy |
| Couple dashboard | `/app` | Operational tool (Filament); clarity over ceremony |
| Guest invitation | `/e/{slug}` / `/e/{slug}/{token}` | The product experience guests remember |
| Onboarding | `/onboarding` | Guided first setup; calm, short steps |
| Referral marketing | `/referral-program` | Same marketing system as landing |

---

## 4. Information architecture

### Couple journey

1. Land on marketing → enquire or start onboarding  
2. Sign up (optional referral cookie) → 3-step wedding wizard  
3. Invitation created **inactive** until admin activation  
4. Couple configures details, guests, media in `/app`  
5. Share personal/public links once live  
6. Monitor RSVPs, messages, visits; send push; earn referrals  

### Guest journey

1. Open personal or public link  
2. Optional reveal animation (once per session)  
3. Read invitation (hero → details → schedule → gallery → RSVP)  
4. Optional: message page, push opt-in page  
5. Return later via same link for updates  

### Content hierarchy (guest page)

1. Couple names & date (always first)  
2. Primary CTA: RSVP (when relevant)  
3. Schedule & location  
4. Gallery / atmosphere  
5. Music, messages, secondary actions  

Never bury the date or RSVP behind decorative chrome.

---

## 5. Brand & visual system

### Brand voice

- Warm, direct, slightly formal — like a well-written invitation card  
- Prefer short sentences and concrete verbs  
- Avoid em dashes, paired-negation slogans (“No X, no Y”), and AI-template headline patterns  
- Localize meaning, not word-for-word English structure (especially bs / de)

See also: [Landing page redesign plan](../plans/landing-page-redesign.md).

### Marketing palette

Established landing identity (keep; do not replace with generic purple/cream AI defaults):

| Token | Value | Use |
|-------|-------|-----|
| Night | `#1a1208` | Page background, deep surfaces |
| Ivory | `#faf6ee` | Headings, primary text on dark |
| Gold | `#c9a227` | Accents, CTAs, focus |
| Sand | `#d4c4a8` | Body / secondary text |

Typography (marketing direction): **Poppins** via Bunny Fonts (`400/500/600/700`). Build a deliberate type scale (weight + size + tracking), not three repeated sizes.

### Invitation design system

Guest pages are driven by **theme × template × reveal**, not the marketing palette.

**Themes** (color, type, atmosphere):

| Key | Character |
|-----|-----------|
| `amber-gold` | Warm gold, classic elegance |
| `royal-wedding` | Navy + gold formality |
| `lavender-dream` | Soft purple romance |
| `winter-magic` | Cool white / ice |
| `pearl-white` | Minimal clean white |
| `dusty-rose` | Rose / mauve warmth |

Each theme exposes CSS variables (`--color-bg`, `--color-text`, `--color-primary`, fonts, soft surfaces). Invitation UI must use those tokens, never hard-coded marketing hex values.

**Templates** (structure):

| Key | Layout idea |
|-----|-------------|
| `classic` | Centered, formal, traditional rhythm |
| `editorial` | Magazine asymmetry, bold type |
| `story` | Scroll narrative, section-by-section |

**Reveals** (one-shot, Alpine + `sessionStorage`):

| Key | Metaphor |
|-----|----------|
| `envelope` | Envelope opens |
| `wax-seal` | Seal breaks |
| `curtain` | Curtains part |
| `polaroid` | Photo develops |

Reveals should feel short and tactile; never block access if JS fails.

### Couple / admin UI

Filament 4 panels with a light custom theme (`resources/css/filament/app/theme.css`). Prioritize scanability: RSVP counts, recent messages, visit stats. Do not force wedding ornamentation into CRUD screens.

### Motion principles

- Marketing: 2–3 intentional entrance motions (fade/slide), not continuous noise  
- Invitation: reveal once; then subtle section fades if needed  
- Dashboard: minimal motion; prefer instant feedback  
- Respect `prefers-reduced-motion`

---

## 6. UX principles by surface

### Marketing

- First viewport = one composition: brand, one headline, one supporting line, CTA group, dominant visual  
- One job per section  
- Avoid identical icon-card grids stacked four times  
- Pricing cards are OK (comparison is the job); feature checklists should not look like benefit cards  
- Live demos (Islamic / Christian themes) are the product proof — give them visual weight  

### Couple dashboard

- Default landing: status of *their* wedding (activation, RSVPs, messages)  
- Guest list actions (add, share link, RSVP) within two clicks  
- Push compose flow: clear audience + preview of what guests receive  
- Referral: link, QR, earnings — no hunting  

### Guest invitation

- Mobile-first; thumb-reachable RSVP  
- Personalization visible immediately when tokenized (“Dear Ana…”)  
- Forms short; media uploads resilient on slow networks  
- Public vs personal link modes must not leak other guests’ data  

---

## 7. Feature design notes

| Feature | Design expectation |
|---------|-------------------|
| Onboarding wizard | 3 short steps; progress visible; can finish without perfection |
| RSVP | States: pending / attending / declined (+ optional companions) |
| Guest messages | Text / photo / audio; couple moderation in panel |
| Push | Opt-in only on dedicated page; honest permission copy |
| Link analytics | Useful trends for couples; hashed IPs; no creepy precision |
| Save the date | Lighter sibling of full invitation when used |
| PWA | Installable guest experience; offline grace where practical |
| Referral | Couple-facing clarity + admin payout workflow |

Pricing tiers (Basic → Deluxe) are **informational** on the site; activation remains operator-led until self-serve payments exist.

---

## 8. Accessibility & quality bar

- Semantic headings and focus states on all interactive elements  
- Color contrast on gold/ivory over night backgrounds  
- Touch targets ≥ 44px on invitation CTAs  
- Language switcher always available on marketing; invitation locale follows event / guest context  
- PDF / brochure paths must remain readable when emoji are converted to images  

---

## 9. Design constraints (current product)

These are product-design constraints, not just engineering debt:

1. **Manual activation** — copy and UI must not promise instant “go live after checkout”  
2. **No in-app payments** — pricing is informative; contact / onboarding is the conversion path  
3. **One wedding per user** — IA assumes a single active event  
4. **Filament couple UI** — feels operational; polish within Filament patterns rather than inventing a second design system  
5. **Multilingual** — every user-facing string goes through lang files (`bs` / `en` / `de`)  

---

## 10. Design backlog (aligned with product opportunities)

Prioritized from a design perspective:

1. Landing redesign (copy + section rhythm) — see plan doc  
2. Stronger first-run onboarding preview of the live invitation  
3. Clearer inactive vs live states for couples  
4. Guest RSVP confirmation moment (reassurance after submit)  
5. Self-serve checkout UX (when payments land)  
6. Expanded theme/template gallery with real previews  
7. Post-wedding album experience (extends invitation relationship)  

---

## 11. File map (design-relevant)

| Area | Location |
|------|----------|
| Marketing views | `resources/views/landing/` |
| Landing layout / CSS | `resources/views/layouts/landing.blade.php`, `resources/css/app.css` |
| Landing copy | `lang/{bs,en,de}/landing.php` |
| Invitation themes / templates / reveals | `resources/views/components/invitation/` |
| Couple / admin UI theme | `resources/css/filament/app/theme.css` |
| Invitation JS / Alpine | `resources/js/` |

---

*Living document — update when brand, surfaces, or invitation systems change. Companion to [project-context.md](../architecture/project-context.md).*
