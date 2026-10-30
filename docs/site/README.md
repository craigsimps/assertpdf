# Landing page

One static page for the toolkit, built from the [brand pack](../brand/README.md) and the [content brief](../launch/landing-brief.md). There is no build step. Fonts are self-hosted and the page makes no third-party requests. The analytics snippet is commented out; external links go to GitHub.

## Files

| Path | Purpose |
| --- | --- |
| `index.html` | The page. All landing-page copy lives here; edit the text in place. |
| `styles.css` | Styling, with the brand tokens copied from `docs/brand/tokens.css`. |
| `assets/` | Mark and wordmark SVGs copied from `docs/brand/logo/`. |
| `favicon.svg` | Theme-aware favicon. |
| `fonts/` | IBM Plex Sans 400/500/600 and IBM Plex Mono 400 (latin woff2) with their SIL OFL 1.1 licences. |
| `.nojekyll` | Tells GitHub Pages to serve the folder as-is. |

## Preview locally

```sh
php -S 127.0.0.1:8080 -t docs/site
```

Then open <http://127.0.0.1:8080/>. Opening `index.html` directly from the file system also works.

## Before launch

Search `index.html` for `TODO-` and resolve each item:

- `TODO-DEMO`: the demo link points at the README's demo section until the demo repository is public.
- `TODO-PACKAGIST`: the install command assumes `craigsimps/laravel-pdf-testing` is on Packagist.
- `TODO-ANALYTICS`: a GoatCounter snippet is present but commented out. It stays disabled unless Craig opts in.

## Publishing (requires Craig’s approval)

GitHub Pages is not enabled and the repository is private. After Craig approves publication, choose one of these options:

1. **Actions deploy of this folder.** `.github/workflows/pages.yml` uploads `docs/site/` as a Pages artifact. It runs only when started by hand (`workflow_dispatch`), so it does nothing until Pages is switched to the "GitHub Actions" source and someone runs it.
2. **Branch deploy.** Pages can serve `/docs` from a branch, which would publish `docs/site/` at `/site/` alongside the Markdown in `docs/`. Option 1 serves the page at the site root.

For either option, Craig must approve making the repository public. Set a custom domain if needed. For option 1, run the workflow; for option 2, configure the branch source. Check the page at the Pages URL.

## Accessibility checklist

Semantic landmarks (`header`, `nav`, `main`, `section` with headings, `footer`), a skip link, one `h1`, visible focus rings on every control, 44 px targets, code blocks that scroll rather than overflow, light and dark colour pairs that meet WCAG AA (ratios in the brand pack), and no motion beyond smooth scrolling, which respects `prefers-reduced-motion`.
