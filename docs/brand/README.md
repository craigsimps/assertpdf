# Laravel PDF Testing: brand identity pack

Version 0.1, prepared 2026-10-30 for [milestone 3, part 2](https://github.com/craigsimps/laravel-pdf-testing/issues/3). This pack is the source for the landing page in `docs/site/` and for any README, demo or social artwork. Publication requires Craig’s approval.

Copy edited by Codex in a separate session. Independent design review is still required before acceptance.

## Contents

| Path | What it is |
| --- | --- |
| `README.md` | This document: direction, logo, type, colour, usage rules. |
| `tokens.css` | The palette, type scale and shape values as CSS custom properties, light and dark. |
| `contrast.mjs` | Checks every text/background pair below against WCAG 2.2. Run `node docs/brand/contrast.mjs`. |
| `logo/mark.svg`, `logo/mark-dark.svg`, `logo/mark-mono.svg` | The mark on paper, on a dark ground, and in a single colour (`currentColor`). |
| `logo/wordmark.svg`, `logo/wordmark-dark.svg`, `logo/wordmark-mono.svg` | Mark plus the name. The text is outlined, so no font is needed to display it. |
| `logo/favicon.svg` | 16 to 32 px version without the text lines; switches colours with the system theme. |
| `examples/social-preview.svg`, `examples/social-preview.png` | 1280 by 640 GitHub social preview. |

The font files live with the site at `docs/site/fonts/`, next to their licences.

## 1. Direction

**One line:** test the PDF your app generates.

**What it is.** A free, MIT-licensed Composer dev package. It reads the PDF a Laravel app generates and checks it in Pest or PHPUnit. Text assertions and page counts check the content; rendered snapshots check for visual changes. Keep generator fakes to check that the app requested a PDF.

**Who it is for.** Laravel developers testing an invoice, statement or certificate endpoint where successful generation does not mean the document is correct.

**How it should feel.** Use paper and ink colours, with one accent for "verified". Use monospace for commands and output. The design should fit alongside Pest output and a GitHub README.

**Principles.**

1. **Evidence over assurance.** Show the relevant test output or diff PNG. Any claim on a page must be something the README and the test output can back up. No adoption numbers, testimonials or "trusted by" until they are real.
2. **Quiet and precise.** One typeface family, one accent, 1 px lines, 6 px corners. No gradients, shadows, mascots, emoji or illustration.
3. **Honest about limits.** Keep limitations beside the features, in the same type size. Explain the text-layer requirement and the local runtime. State that snapshots require the same operating system and architecture.

**Voice.** Write plainly and be specific. Short sentences. Name the actual thing: "the invoice total", "page 2", "diff-1.png". Prefer "fails" to "may not pass". Avoid "blazing", "bulletproof", "seamless", "powerful", exclamation marks, and any sentence that would also be true of every other package.

## 2. Name and mark

**Name.** "Laravel PDF Testing" in prose and in the wordmark. The package is `craigsimps/laravel-pdf-testing`; the binary and config key are `pdf-testing`. Do not shorten to "LPT" or stylise the capitals.

**Mark.** A page with a folded corner, two text lines and a tick: a document that has been read and checked. Drawn with 2 px strokes on a 32 px grid. The tick is the only coloured element.

| File | Use |
| --- | --- |
| `logo/mark.svg` | Ink `#16222E` page, Teal `#0B6E66` tick, on Paper or Paper 2. |
| `logo/mark-dark.svg` | Text `#E8EDF2` page, Teal light `#5FD3C6` tick, on Ground or Surface. |
| `logo/mark-mono.svg` | Single colour via `currentColor`: print, badges, coloured backgrounds. |
| `logo/favicon.svg` | Below 24 px. Drops the text lines and thickens the strokes. |

**Wordmark.** The mark at 44 px with the name set in IBM Plex Sans SemiBold at 40 px, tracking -0.02 em, cap height centred on the mark. The SVGs carry the letters as outlines.

**Rules.**

- Clear space: half the mark's height on every side.
- Minimum size: mark 16 px, wordmark 120 px wide. Below that use the mark alone.
- Keep the proportions. Do not recolour the page, rotate or skew the mark, add a shadow, or set the name in another typeface.
- Do not place the mark next to the Laravel logo, and do not use the Laravel logo anywhere in this project's material.

**Trademark.** "Laravel" is a trademark of Taylor Otwell. The word appears descriptively in the package name, the way the Laravel ecosystem commonly names packages. Pages and READMEs carry the line "Laravel is a trademark of Taylor Otwell. This package is independent and not endorsed by Laravel." Do not imply official status.

## 3. Type

| Role | Family | Licence | Files shipped |
| --- | --- | --- | --- |
| Headings, body, UI | [IBM Plex Sans](https://github.com/IBM/plex) | SIL Open Font License 1.1 | `docs/site/fonts/ibm-plex-sans-latin-{400,500,600}-normal.woff2` |
| Code, commands, labels | [IBM Plex Mono](https://github.com/IBM/plex) | SIL Open Font License 1.1 | `docs/site/fonts/ibm-plex-mono-latin-400-normal.woff2` |

The woff2 files are the latin subsets from the `@fontsource/ibm-plex-sans` and `@fontsource/ibm-plex-mono` 5.3.0 packages, about 86 KB in total. The OFL text sits beside them as `OFL-IBM-Plex-Sans.txt` and `OFL-IBM-Plex-Mono.txt`. The OFL permits self-hosting and redistribution; it forbids selling the fonts on their own, which we do not do. No paid or proprietary fonts are used anywhere.

Fallbacks: `system-ui, -apple-system, "Segoe UI", Roboto, sans-serif` and `ui-monospace, SFMono-Regular, Menlo, Consolas, monospace`. The design must still work in the fallbacks, so nothing depends on exact glyph widths.

**Scale** (desktop; the site drops the display size to 34 px under 640 px wide):

| Style | Size / line height | Weight | Notes |
| --- | --- | --- | --- |
| Display | 48 / 1.1 | 600 | tracking -0.02 em; the one big line per page |
| Heading 2 | 28 / 1.2 | 600 | tracking -0.01 em |
| Heading 3 | 20 / 1.3 | 600 | |
| Body | 17 / 1.6 | 400 | measure 60 to 70 characters, colour Ink or Ink 2 |
| Small | 15 / 1.5 | 400 | cards, lists |
| Caption | 14 / 1.5 | 400 | Ink 3 |
| Label | 13 / 1.4 | 500 | mono, eyebrow lines; the only place uppercase is allowed |
| Code | 14 / 1.6 | 400 | mono, never wraps; scrolls sideways on phones |

Sentence case everywhere, including buttons and headings. Mono is for anything a reader could type or copy: commands, paths, versions, assertion names, hex values.

## 4. Colour

Ratios are WCAG 2.2 contrast of the text colour on the named ground, measured by `contrast.mjs`. AA needs 4.5:1 for normal text and 3:1 for large text and UI boundaries; AAA needs 7:1.

### Light (default)

| Token | Hex | Role | On Paper | On Paper 2 |
| --- | --- | --- | --- | --- |
| Paper | `#FFFFFF` | page ground | | |
| Paper 2 | `#F3F5F7` | code blocks, panels, alternate bands | | |
| Line | `#D5DBE1` | decorative rules and card borders only (1.4:1, not for meaning) | | |
| Ink | `#16222E` | headings, body | 16.1:1 AAA | 14.8:1 AAA |
| Ink 2 | `#3F4C59` | secondary text | 8.8:1 AAA | 8.0:1 AAA |
| Ink 3 | `#5B6875` | captions, labels | 5.7:1 AA | 5.2:1 AA |
| Teal | `#0B6E66` | links, primary buttons, the tick | 6.1:1 AA | 5.6:1 AA |
| Teal hover | `#095A54` | hover, focus ring | 8.1:1 AAA | |
| Diff red | `#B42318` | failures and diffs only | 6.6:1 AA | 6.0:1 AA |

Filled pairs:

| Text | On | Ratio |
| --- | --- | --- |
| Paper `#FFFFFF` | Teal `#0B6E66` (primary button) | 6.1:1 AA |
| Teal text `#075E57` | Teal tint `#E3F3F1` ("passed" notes) | 6.7:1 AA |
| Diff red `#B42318` | Diff tint `#FDECEA` (failure notes) | 5.8:1 AA |
| Paper `#FFFFFF` | Diff red `#B42318` | 6.6:1 AA |

### Dark (`prefers-color-scheme: dark`)

| Token | Hex | Role | On Ground | On Surface |
| --- | --- | --- | --- | --- |
| Ground | `#0F171F` | page ground; also the light theme's terminal blocks and footer | | |
| Surface | `#182330` | panels, code blocks | | |
| Line (dark) | `#2B3A49` | decorative rules only | | |
| Text | `#E8EDF2` | headings, body | 15.3:1 AAA | 13.5:1 AAA |
| Text 2 | `#AEBBC8` | secondary text | 9.2:1 AAA | 8.1:1 AAA |
| Muted | `#8A98A6` | captions | 6.1:1 AA | 5.4:1 AA |
| Teal light | `#5FD3C6` | links, the tick; Ground text on it 10.0:1 | 10.0:1 AAA | 8.8:1 AAA |
| Diff light | `#F28B82` | failures and diffs only | 7.6:1 AAA | 6.7:1 AA |

### Extension after design feedback (2026-10-30)

Craig asked for the accent used with more confidence and for the page to show the product's own diff look. Three roles were added; `contrast.mjs` checks them.

| Token | Hex | Role | Ratios |
| --- | --- | --- | --- |
| Teal deep | `#06433E` | full-bleed colour-blocked band (the install section) | Paper 11.2:1 AAA, Text 9.5:1 AAA, Text 2 5.7:1 AA, Teal light 6.2:1 AA |
| Diff mark | `#FF0000` | graphic only: the red pixelmatch writes into `diff-N.png`, used in the hero's snapshot illustration | 4.0:1 on Paper as a graphic; never for text |
| Diff faded | `#C9CED3` | graphic only: unchanged pixels as the diff PNG shows them | decorative |

Ground (`#0F171F`) is now also used as the header and hero background in both themes, with Text, Text 2, Teal light and Diff light on it (ratios in the dark table above).

Rules:

- Red means a failure or a diff, the same as the toolkit's own `diff-N.png`. Never use it for emphasis, badges of passing states, or decoration.
- Teal is the only accent. One per view is enough: a button, a link colour, the tick.
- Never convey meaning by hue alone. Pass/fail notes also differ in wording and in lightness.
- Line colours fail 3:1 on purpose; they are decoration. Anything a user must perceive as a boundary (button outline, input border, focus ring) uses Ink or Teal.

## 5. Shape, spacing and interaction

- Corner radius 6 px on cards, code blocks and buttons; 4 px on badges.
- 1 px borders in Line; no shadows, no gradients, no background images.
- **Signature image:** the snapshot triptych. Baseline page, this run's render, and the diff PNG with the changed pixels in Diff mark red on a faded page, captioned with the toolkit's real failure line. It is drawn in HTML and CSS, never a stock image, and is the main product illustration.
- **Motifs:** print crop marks (thin L-shaped corners in Teal light) around the signature image and before section titles; a faint pixel grid behind the diff page. Both are decorative, drawn in CSS, and never carry meaning.
- **Rhythm:** sections change shape rather than only colour: a dark split hero, a full-bleed code-and-terminal pair, a compact comparison strip, a Teal deep band, a numbered workflow. Avoid stacking same-width cards on alternating backgrounds.
- Spacing on an 8 px step: 8, 12, 16, 24, 32, 48, 72.
- Controls are at least 44 px tall. Primary button: Teal fill, Paper text, weight 500. Secondary: 1 px Ink outline, Ink text.
- Focus: `outline: 3px solid var(--teal-hover); outline-offset: 2px` on `:focus-visible`, never removed.
- Dark mode follows the system; there is no toggle.

## 6. Usage examples

**README header.** The wordmark at 240 px wide, then the one-line description, then badges. Badges use Paper 2 for the label cell and Teal or Ink for the value cell; never red for a passing state.

```md
<img src="docs/brand/logo/wordmark.svg" alt="Laravel PDF Testing" width="240">

Pest and PHPUnit assertions against real PDF text, pages and rendered snapshots.
```

Where a dark-mode variant is wanted on GitHub:

```md
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="docs/brand/logo/wordmark-dark.svg">
  <img src="docs/brand/logo/wordmark.svg" alt="Laravel PDF Testing" width="240">
</picture>
```

**GitHub social preview.** `examples/social-preview.png` (1280 by 640): Ground background, mark and name top left, one display line, one line of description, the Composer command in mono Teal light. Upload it under the repository's settings when the repo goes public; it is not set by this pack.

**Landing page.** `docs/site/index.html` and `docs/site/styles.css` are built from this pack: a Ground header and hero carrying the snapshot triptych, a code-and-terminal split, a Teal deep install band, Ink type on Paper elsewhere, IBM Plex self-hosted, dark mode via the tokens.

**Terminal panels.** Ground background, Text for output, Teal light for keywords and passes, Diff light for failures, Text 2 for strings and dimmed lines. Use the toolkit's real message formats: `Expected PDF to contain "120.00".`, `Expected PDF to have 1 page(s); found 2.`, `PDF snapshot page 1: N changed pixels (allowed 0). Diff: …/diff-1.png`.

**Demo app.** When the demo gets any styling, reuse `tokens.css`: Ink on Paper for the invoice page, mono for amounts and invoice numbers. The invoice PDF itself stays plain, since it is a fixture, not marketing.

**Terminal and docs.** In screenshots of test output, keep the terminal's own colours. Do not restyle Pest's output to match the brand.

**Pest failure notes and callouts.** Teal tint with Teal text for "what passed", Diff tint with Diff red for "what failed", each with a bold first word so the meaning survives without colour.

## 7. Do and don't

Do: lead with a failure the tool catches; show a real assertion; state Node, PHP and Pest versions; use the mark alone at small sizes; keep the tick as the only coloured element in the logo.

Don't: invent numbers or testimonials; use the Laravel logo or imply endorsement; add a hero illustration, gradient or 3D render; use red for anything but failures; set the wordmark in another typeface; put emoji in headings or buttons.

## 8. Checking changes

- `node docs/brand/contrast.mjs` exits non-zero if any listed pair falls below its target. Add a pair when you add a colour role.
- `composer lint` syntax-checks `contrast.mjs` with the other Node scripts.
- The design canvas used to draft this pack is private at the link recorded on the issue.
