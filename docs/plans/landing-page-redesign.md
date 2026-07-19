# NasDan Landing Page Redesign Plan

> Part of [docs/](../README.md). Design system: [project-design.md](../design/project-design.md).

Goal: make the landing page read as something a human designer and copywriter built, not a generated template. This covers two problems found in the current page: the copy has the rhythm and habits of AI writing, and the layout leans on the same generic template pattern repeated section after section. Font: Poppins, sans-serif, everywhere.

## 1. What's currently giving it away

**The em dash habit.** Every single body paragraph in `lang/en/landing.php`, `lang/bs/landing.php`, and `lang/de/landing.php` uses " — " to join two clauses, usually in an "X — and here's the payoff" shape:

- "Send each guest a personalized link — as soon as they open the invitation, their name is already there."
- "WhatsApp, Viber or Telegram — choose the platform and send the invitation directly."
- "You know exactly who is coming, who is not — and who has not responded yet."

That's not one stray sentence, it's the sentence template for the whole page. Real copy would use periods, commas, "so," or just a shorter sentence. New rule: no em dashes anywhere. If a short dash is genuinely useful (a date range, an inline aside), use a plain hyphen with spaces around it, "-", not "–" or "—", and only sparingly.

**Paired-negation cadence.** "No confusion, no anonymity." "No copying links, no forgotten guests." "Who is coming, who is not." This rule-of-two/three balanced-clause habit shows up in almost every benefit line. It reads as generated because it's the same rhetorical trick every time. Some lines should just make one point and stop.

**Rhetorical headline framing.** "What a photo cannot do" and "Your wedding story deserves more than a photo in a chat" are both "here's the old broken way, here's why we're better" headline templates, a very common AI pitch structure. Worth keeping one strong headline like this, not building the whole page's structure around it.

**Repeated adverbs.** "immediately," "directly," "personalized" show up over and over across benefit, interaction, and step copy. Fine once or twice, noticeable at scale.

**The template layout.** Every section (benefits, guest interaction, how it works, pricing features) is the identical component: centered `h2` + subtitle, then a grid of `landing-card` boxes, each with a small circle containing an icon, a bold title, a paragraph. Benefits and the pricing feature checklist even reuse the exact same checkmark-in-a-circle icon for every single item, regardless of what the item is about. Four sections in a row using one repeated card pattern is the strongest visual tell that this is a scaffolded template rather than a designed page.

## 2. Copy direction

Rewrite each paragraph to break the em-dash/paired-negation pattern. Vary sentence length on purpose, short sentence, then a longer one, not the same two-clause shape every time. Example rewrites (English; bs/de get the equivalent treatment once English is approved):

- Hero subtitle, before: "A personalized web invitation with countdown, RSVP, day schedule and venue photos — ready to share in three steps."
  After: "A web invitation with a countdown, RSVP, day schedule and venue photos. Set it up and share it in three steps."

- Benefit 1, before: "Send each guest a personalized link — as soon as they open the invitation, their name is already there. No confusion, no anonymity."
  After: "Every guest gets their own link. Their name is already on the page when they open it, so there's no mix-up about who's invited."

- Benefit 2, before: "WhatsApp, Viber or Telegram — choose the platform and send the invitation directly. No copying links, no forgotten guests."
  After: "Send straight from WhatsApp, Viber, or Telegram. You pick the app, the invitation goes out, nobody gets left off the list by accident."

- Benefit 3, before: "Every RSVP response appears immediately in your panel. You know exactly who is coming, who is not — and who has not responded yet."
  After: "Every RSVP shows up in your panel right away, so you always know who's coming and who still hasn't answered."

- Step 2, before: "Your invitation link appears immediately in your panel. Preview it anytime — it goes live once payment is confirmed and our team activates it."
  After: "Your link shows up in your panel right away. You can preview it whenever you like; it goes live after payment is confirmed and our team switches it on."

Apply the same logic line by line through benefits, guest interaction, steps, and pricing features in all three language files. Two things to watch while rewriting bs/de: don't just mechanically swap the dash for a comma in translation, since German and Bosnian have their own natural connector words (Bosnian "pa," "i to," German "und zwar," subordinate clauses), so each language should be edited on its own terms, not forced to match the English sentence structure word for word.

Also worth a sitewide note (not landing-only): the same em-dash habit appears in `lang/*/notifications.php`, `lang/*/app.php`, `lang/*/invitation.php`, `lang/*/referrals.php`, `lang/*/schedule.php`. Out of scope for this landing-page pass, but flagging it since it's the same pattern and you may want it fixed at some point too.

## 3. Visual direction

**Typography.** Poppins, sans-serif, replacing Cormorant Garamond and Lora on the marketing page. Load it the same way the current fonts are loaded (bunny.net, so no third-party tracking):
`https://fonts.bunny.net/css?family=poppins:400,500,600,700`
Update `.landing-heading` and `.landing-body` in `resources/css/app.css` to `font-family: 'Poppins', sans-serif;`. To avoid the page reading as "default Tailwind + one Google Font," build an actual type scale instead of reusing 2-3 sizes everywhere: headings at 600/700 weight, body copy at 400, small labels (like the "01/02" step numbers or pricing tags) at 500 with slightly wider letter-spacing. One typeface can still look designed if the weight and size pairing is deliberate.

**Break the repeated card pattern.** Don't run four sections in a row through the same centered-heading + icon-card-grid component. Suggested per-section treatment:
- Benefits: keep a grid, but drop the identical checkmark-in-circle icon for every item, use a distinct small icon per benefit (name tag, chat bubble, live checkmark, seating grid) so each card is visually tied to what it's about.
- Guest interaction: switch to an asymmetric two-column layout (text on one side, a simple mock message/photo preview on the other) instead of a third identical card grid.
- How it works: keep the numbered steps, but present them as a horizontal connected line/timeline rather than three interchangeable bordered boxes, so it doesn't read as the same card component as benefits.
- Pricing: keep the plan cards (they're doing a different job, comparison, so repetition here is fine), but move the "all plans include" list out of a bordered box that echoes the benefit cards, a plain two-column list under a divider reads less templated.

**Section rhythm.** Currently alternating background is just `bg-transparent` / `bg-[#2a1f0f]/50` on every other section, mechanical. Consider varying which sections get a background tint based on content weight (e.g., give the demo section more visual presence since it's the actual product proof) rather than strict alternation.

Keep the dark/gold palette (`#1a1208`, `#faf6ee`, `#c9a227`, `#d4c4a8`), it's a real brand identity, not an AI tell. The tell is the repeated component, not the color choice.

## 4. Files to touch

| File | Change |
|---|---|
| `resources/views/layouts/landing.blade.php` | Swap bunny.net font link to Poppins |
| `resources/css/app.css` | `.landing-heading` / `.landing-body` → Poppins; add type-scale utility classes for weight/tracking variants |
| `resources/views/landing/sections/hero.blade.php` | Minor copy tweak only (headline can stay, subtitle rewritten) |
| `resources/views/landing/sections/benefits.blade.php` | Per-benefit icon instead of shared checkmark; copy rewrite |
| `resources/views/landing/sections/guest-interaction.blade.php` | Layout change to asymmetric two-column; copy rewrite |
| `resources/views/landing/sections/how-it-works.blade.php` | Timeline-style layout instead of card grid; copy rewrite |
| `resources/views/landing/sections/pricing.blade.php` | Restyle "all plans include" list; copy rewrite on feature 4 |
| `lang/en/landing.php`, `lang/bs/landing.php`, `lang/de/landing.php` | Full copy pass removing em dashes and paired-negation phrasing |

## 5. Implementation order

1. Font swap first (layout + CSS), quick and low-risk, confirms Poppins renders correctly across weights before anything else changes.
2. English copy rewrite in `lang/en/landing.php`, reviewed against this plan.
3. Bosnian and German copy passes, done natively in each language rather than translated from the new English line by line.
4. Benefits section: new icon set + updated markup.
5. Guest interaction section: new two-column layout.
6. How it works: timeline layout.
7. Pricing: restyle the feature list block.
8. Full visual QA pass (see below).

## 6. Verification checklist

- `grep -rn "—\|–" lang/en/landing.php lang/bs/landing.php lang/de/landing.php` returns nothing.
- No two consecutive sections use the exact same card/icon/grid markup.
- No benefit or feature item reuses the same icon as another item in the same grid.
- Poppins loads and renders on hero, headings, and body text (check network tab / rendered font in browser devtools).
- Read the full page copy out loud, flag any sentence that still has the "X, and here's why that matters" two-clause shape.
- Mobile check: timeline and two-column sections collapse cleanly on small screens.
