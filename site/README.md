# Landing site (Astro)

The static landing page for `craigsimps/laravel-pdf-testing`, built with [Astro](https://astro.build) and ready for Cloudflare Pages. It reproduces the design approved on 2026-10-30 ([issue #3](https://github.com/craigsimps/laravel-pdf-testing/issues/3)) and replaces the hand-written `docs/site/` page; see [issue #6](https://github.com/craigsimps/laravel-pdf-testing/issues/6).

This folder is `export-ignore`d in `.gitattributes`, so it never ships in the Composer package.

## Layout

| Path | Purpose |
| --- | --- |
| `src/pages/index.astro` | The landing page. All landing-page copy lives here; edit the text in place. Copy changes go through the Codex edit (AGENTS.md, Copywriting). |
| `src/pages/404.astro` | Not-found page. |
| `src/layouts/Base.astro` | Document shell: meta, canonical, Open Graph, font preloads, stylesheet imports. |
| `src/styles/site.css` | Component rules from the approved page plus three site-only variables. |
| `src/styles/fonts.css` | `@font-face` rules for the self-hosted fonts. |
| `../docs/brand/tokens.css` | Source of truth for colours, type and shape; imported first by the layout. Do not copy its values here. |
| `public/fonts/` | IBM Plex Sans 400/500/600 and IBM Plex Mono 400 (latin woff2) with their SIL OFL 1.1 licences. |
| `public/og/social-preview.png` | Open Graph image, copied from `docs/brand/examples/`. |
| `public/_headers` | Cloudflare Pages security and caching headers, including a CSP with `script-src 'none'`. |
| `public/robots.txt` | Allows crawling and points at the sitemap. |
| `astro.config.mjs` | `site` origin, sitemap integration, external stylesheet output. |

The site ships no JavaScript. Styles are emitted as one external file so the CSP can stay at `style-src 'self'`.

## Develop and build

Requires Node 24.21.0 (see `.node-version`) and npm 11.

```sh
cd site
npm ci
npm run dev        # http://localhost:4321
npm run build      # writes dist/
npm run preview    # serves dist/ on http://localhost:4321
```

`npm run build` is also run by `.github/workflows/site.yml` on every push and pull request.

## Cloudflare Pages settings

Create the project from this repository with these values. Nothing here creates it; that step is Craig's.

| Setting | Value |
| --- | --- |
| Framework preset | Astro |
| Root directory | `site` |
| Build command | `npm run build` |
| Build output directory | `dist` |
| Node version | `24.21.0` (read from `site/.node-version`, or set `NODE_VERSION=24.21.0`) |
| Environment variable | `SITE_URL=https://<final-domain>` once the domain is known |
| Production branch | `main` |

`SITE_URL` feeds the canonical link, Open Graph URLs and the sitemap. Until it is set, the build uses the placeholder `https://laravel-pdf-testing.pages.dev`, which also appears in `public/robots.txt`; update both when the real origin is known.

## Before launch

Search `src/pages/index.astro` for `TODO-` and resolve each item:

- `TODO-DEMO`: the demo link points at the README's demo section until the demo repository is public.
- `TODO-PACKAGIST`: the install command assumes `craigsimps/laravel-pdf-testing` is on Packagist.
- `TODO-ANALYTICS`: analytics are out of scope until Craig decides; the GoatCounter snippet from the approved page is kept as a comment in the source and is not emitted.

Astro strips HTML comments from the output, so these notes never reach visitors.

## Checks

- Visual: compare `npm run preview` against the approved page at 1440 px and 390 px, light and dark. The reference renders and pixel diffs from the build handoff are in `docs/launch/previews/`.
- Accessibility: axe-core on the preview, 0 violations expected.
- Lighthouse: 95 or above in performance, accessibility, best practices and SEO.
