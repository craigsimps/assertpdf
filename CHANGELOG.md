# Changelog

## 0.1.0 — unreleased

First free release, prepared as local candidate `v0.1.0-rc.1`. No public tag or Packagist release yet. Release date is deliberately unset until publication is approved.

### Changed

- Renamed the product to AssertPDF before first release: package `craigsimps/assertpdf` (was `craigsimps/laravel-pdf-testing`), namespace `AssertPdf\` (was `PdfTesting\`), binary `bin/assertpdf`, config key `assertpdf`, publish tag `assertpdf-config`, Artisan command `assertpdf:install` and environment variable `ASSERTPDF_UPDATE_SNAPSHOTS`.

### Added

- Pest `toHavePdfText`, `toHavePdfPageCount` and `toMatchPdfSnapshot` expectations, and equivalent `AssertPdf\PdfAssertions` methods for PHPUnit.
- Assertions against actual PDF bytes or local files, with local pdf.js text extraction and page rendering. No hosted renderer, OCR or browser required by the toolkit.
- Explicit, transactional visual-baseline updates, per-page pixel allowances, one rectangular mask and actual/diff PNG artifacts. Baselines record renderer, Node, OS and architecture; cross-environment comparisons are rejected.
- Native Laravel test/HTTP, binary-file and streamed response inputs; repeatable flushed stream capture and HTTP-aware input errors. Invalid PDFs raise runtime errors even in negated expectations.
- Optional Spatie Laravel PDF and Laravel-Dompdf object inputs without requiring either generator in consumer applications.
- Auto-discovered Laravel provider, configurable snapshot root/default allowance, Composer renderer installer and Artisan alias.
- PHP 8.3/8.4, Laravel 12 and compatible Pest 3/4 / PHPUnit 11.5/12 CI matrix, including generator-absent and fresh-consumer installation checks.

### Release policy

During 0.x, a minor release may change the API; patch releases fix bugs. The `^0.1` constraint stays below 0.2. Renderer changes require deliberate baseline review. See the README for supported version combinations and limitations.
