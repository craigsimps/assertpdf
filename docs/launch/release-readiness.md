# v0.1.0 release readiness

Prepared 2026-10-30 for [milestone 3, part 1](https://github.com/craigsimps/assertpdf/issues/3). This is a readiness record, not publication authorisation.

## Identity and compatibility

The naming decision below was superseded on 2026-10-30 by the rename to AssertPDF ([#8](https://github.com/craigsimps/assertpdf/issues/8)). The package is now `craigsimps/assertpdf` with namespace `AssertPdf\`. Original decision: Keep Composer name **`craigsimps/laravel-pdf-testing`** and PHP namespace **`PdfTesting\`**. The name matches the existing repository and installation contract. The namespace is already used by the public assertions, provider and examples; renaming it now adds consumer churn without a demonstrated benefit. The generic namespace does not imply ownership of a trademark or another package. The npm renderer package remains private; it is not a separate npm release.

Keep the existing local renderer and optional generator boundaries. No new architecture or dependency tradeoff was introduced; retaining these established choices did not require a Counselors consultation. The README and CI define the verified support targets, not every combination Composer could theoretically resolve. No Laravel 11/13 or PHP 8.5 support is claimed.

The repository uses Composer's VCS-derived version, not a hard-coded `version` field. `v0.1.0-rc.1` is a local candidate tag; `v0.1.0` remains an unreleased changelog entry. In 0.x, minor releases can break compatibility; patch releases are fixes. Do not push either tag until Craig authorises publication.

## Licence check

Checked the root `LICENSE`, `composer.json` / lockfile, `composer licenses --format=json`, npm lockfile package licences, and installed pdf.js asset licences on 2026-10-30. This inventory is not legal advice.

- Toolkit code: MIT, copyright 2026 Craig Simpson; root declaration and full licence agree.
- Required PHP packages: PHPUnit and its components use BSD-3-Clause/MIT; Symfony Process and its contracts use MIT. Their licence files remain with Composer installations.
- Renderer: pdfjs-dist 6.4.299 Apache-2.0; pixelmatch 8.0.0 ISC; pngjs 7.0.0 MIT; @napi-rs/canvas and its platform packages 1.1.0 MIT. Dependencies retain their upstream licence files; the installer does not strip them.
- **pdf.js assets have additional licences.** `node_modules/pdfjs-dist/standard_fonts/LICENSE_FOXIT` is BSD-style; `LICENSE_LIBERATION` is GPL-2.0 with the upstream font/document-embedding exception. `cmaps/LICENSE`, `iccs/LICENSE` and the individual `wasm/LICENSE_*` files must be retained too. The main npm Apache-2.0 label alone is not a complete asset inventory. Rendered documents are covered by the stated font exception, not a promise that all upstream files are MIT.
- Optional/demo Dompdf is LGPL-2.1; its font/SVG libraries include LGPL-2.1-or-later and LGPL-3.0-or-later. They are separate unmodified Composer dependencies, not relicensed as toolkit MIT code. The demo keeps their vendor licences and dependency lockfile. Spatie's PDF/Browsershot packages are MIT. Neither optional generator is shipped as a required toolkit dependency.
- Development/test dependencies include MIT, BSD, Apache and LGPL; Nette's declared alternatives include BSD/GPL. Do not delete their notices or describe the entire installed dependency tree as MIT. Any future redistribution of bundled native binaries/fonts must preserve upstream licences and applicable source/redistribution obligations, not just this summary.

No third-party logo, screenshot, marketing asset or customer invoice was added. The demo invoice uses synthetic data. Brand/font selection for the landing page is deferred to the design pack and needs its own licence check.

## Review and publication gates

1. Required local checks: `composer validate --strict`, `composer lint`, `composer test`; standalone and Testbench installation smoke; separate demo setup/test from its README.
2. Require every hosted matrix row green on the **exact candidate commit**. The issue handoff records commit, counts and run URL. Confirm the local RC tag resolves to that same commit.
3. Independent reviewer verifies a fresh Laravel installation from README alone, inspects the demo and checks that baselines never silently update. Builder verification is not independent review.
4. Claude Code creates the brand identity pack, then landing page and any demo styling. Use `landing-brief.md`; part 1 intentionally contains no design work.
5. Craig explicitly approves visibility, demo remote creation/publication, public tags, Packagist submission and landing go-live. None is done by this branch.
6. Before public launch, replace README/demo/post placeholders with verified URLs, switch install instructions to the published `^0.1` package, set the changelog release date, and record the actual launch-day demand baseline. Publish only after the final reviewed commit passes CI.

Use [demand.md](demand.md) for post-launch measurement and [posts.md](posts.md) for drafts Craig can send. No launch clock starts while the project is private.
