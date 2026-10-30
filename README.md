# Laravel PDF testing toolkit

Pest and PHPUnit assertions that inspect the **real PDF**, not the HTML used to generate it. This is a local, unpublished milestone-1 package; no Laravel service provider or PDF generator is required.

## Requirements and installation

PHP 8.3+, PHPUnit 11.5 or 12, Node **24.21.0**, npm 11, and permission to start a local Node process. Pest is optional (tested with 3.8.6 and 4.7.8). Install as a **dev dependency**. There is no Packagist release yet; from your application:

```sh
composer config repositories.pdf-testing '{"type":"path","url":"/Users/craig/Sites/laravel-pdf-testing"}'
composer require --dev 'craigsimps/laravel-pdf-testing:dev-milestone-1-pdf-expectations' -W
npm ci --ignore-scripts --prefix vendor/craigsimps/laravel-pdf-testing
```

Change the local repository path as necessary. The Node dependencies live **inside this package**, not in your application's node_modules. Re-run that npm command after a Composer reinstall or renderer update. Composer does not run npm automatically. For an existing Pest installation, no registration step is needed. If adding Pest, use a version compatible with your application's PHPUnit version (Pest 3 for PHPUnit 11, Pest 4 for PHPUnit 12).

## Pest: test a real invoice

```php
it('renders the invoice correctly', function () {
    // Or pass the PDF bytes returned by your existing generator.
    $pdf = storage_path('app/invoices/1001.pdf');

    expect($pdf)
        ->toHavePdfText('Invoice 1001')
        ->toHavePdfPageCount(1)
        ->toMatchPdfSnapshot(__DIR__.'/pdf-snapshots/invoice-1001');
});
```

Both readable **local paths** and raw bytes beginning `%PDF-` are accepted. URLs and PHP stream wrappers are rejected. Text is a case-sensitive substring of pdf.js's text layer, with line/page separators; this is not OCR or a reading-order/layout guarantee. A missing string reports the extracted text. Page failures report the actual count. Malformed/unreadable PDFs and renderer setup problems raise a `RuntimeException`, not a mismatch that a negated expectation could accidentally accept.

## Explicit snapshots and one mask

Missing baselines **fail**. Create or replace them intentionally:

```sh
PDF_TESTING_UPDATE_SNAPSHOTS=1 vendor/bin/pest --filter='renders the invoice correctly'
vendor/bin/pest --filter='renders the invoice correctly'
```

Review the PNGs before accepting an update. Commit `manifest.json` and `page-*.png`; ignore the snapshot directory's `artifacts/` subdirectory. Never set the update flag in normal CI. Use one unique directory per snapshot and avoid concurrent writes to the same directory.

On comparison, `artifacts/actual-N.png` contains the latest render. A failing page writes a viewable `artifacts/diff-N.png`, whose path appears in the failure message. Red marks show changed pixels. Baselines are never overwritten without the flag. A matching page removes its previous diff. Page count and page size differences always fail.

Ignore one timestamp box (optional third argument):

```php
expect($pdf)->toMatchPdfSnapshot(
    __DIR__.'/pdf-snapshots/invoice-1001',
    mask: ['page' => 1, 'x' => 20, 'y' => 50, 'width' => 100, 'height' => 25],
    maxDiffPixels: 0,
);
```

Coordinates are **integer pixels at 72 DPI**, from the **top-left of the rendered page**; page numbers start at 1. The mask affects comparison only, not the stored baseline or actual PNG. Out-of-bounds masks are errors. `maxDiffPixels` permits that many changed pixels **per page**, after masking. Pixelmatch's colour threshold is fixed at 0.1 with its default antialiasing handling. The default allowance is zero.

## PHPUnit equivalents

```php
use PdfTesting\PdfAssertions;

PdfAssertions::assertPdfContainsText($pdf, 'Invoice 1001');
PdfAssertions::assertPdfPageCount($pdf, 1);
PdfAssertions::assertPdfMatchesSnapshot($pdf, __DIR__.'/pdf-snapshots/invoice-1001');
// The same optional $mask and $maxDiffPixels parameters are accepted.
```

## Renderer and limits

- Pinned: **pdfjs-dist 6.4.299** (Apache-2.0), **pixelmatch 8.0.0** (ISC), **@napi-rs/canvas 1.1.0** (MIT), **pngjs 7.0.0** (MIT). The npm lockfile includes platform-specific native canvas packages. Upstream licences remain in node_modules.
- Assertions use in-memory PDF data and bundled local fonts, CMaps and WASM. No network request, service, Poppler, Ghostscript or browser is needed. Installation requires network access; assertions do not. The Linux checks run with `--network none`.
- Baselines record the renderer versions, Node version, OS and architecture. Comparison rejects an environment mismatch. Generate and review baselines on the same pinned environment used by your CI; **cross-OS pixel identity is not promised**. Embedded fonts are preferable; system font substitution is disabled.
- Password-protected PDFs, scanned PDFs without a text layer, OCR, hosted storage and cross-OS snapshots are out of scope. This does not replace `Pdf::fake()`, which is still useful for testing that your app requested a PDF.
- This is for trusted application/test PDFs, not an untrusted upload service. Each assertion starts Node with a 60-second timeout. Large documents may require more memory and are not benchmarked in this milestone.

## Toolkit development and Linux checks

```sh
composer install
npm ci --ignore-scripts
composer validate --strict
composer lint
composer test
```

Fixtures include a one-page invoice, two pages, a moved block, changed timestamps, changed dimensions and a deliberately corrupt object tree. `npm run fixtures` regenerates them deterministically.

The same Linux/amd64 checks used by `.github/workflows/tests.yml` can run locally:

```sh
docker build --platform linux/amd64 -f Dockerfile.ci -t pdf-testing-ci .
docker run --rm --network none pdf-testing-ci
```

The test image pins PHP 8.3.30, Node 24.21.0 and Composer 2.8.9 by image digest. It uses Debian Bookworm. The workflow is prepared for future authorised publication; a local container run is **not** a hosted GitHub Actions run.
