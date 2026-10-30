<picture>
  <source media="(prefers-color-scheme: dark)" srcset="docs/logo/wordmark-dark.svg">
  <img src="docs/logo/wordmark.svg" alt="AssertPDF" width="240">
</picture>

# AssertPDF

AssertPDF is a PDF testing toolkit for Laravel. The website is [assertpdf.com](https://assertpdf.com), with source in [craigsimps/assertpdf-com](https://github.com/craigsimps/assertpdf-com). Catch missing invoice totals, unexpected page breaks and moved content in the **PDF your application actually emits**. This Composer dev package adds text, page-count and visual-snapshot assertions to Pest and PHPUnit. It complements generator fakes: a fake proves you requested a PDF; these assertions inspect the resulting bytes.

**Release status:** v0.1.0. Laravel is optional, no PDF generator is installed for consumers, and assertions run locally without a hosted service. Free, [MIT licensed](LICENSE).

```php
it('renders the invoice correctly', function () {
    expect($this->get('/invoices/1001/pdf'))
        ->toHavePdfText('Invoice 1001')
        ->toHavePdfText('120.00')
        ->toHavePdfPageCount(1);
});
```

Requires an existing PDF endpoint; see the [demo](#demo) for a complete app. Add visual snapshots separately using the explicit baseline workflow below.

## Supported versions

| Component | v0.1.0 support target / CI coverage |
| --- | --- |
| PHP | 8.3 and 8.4 (CI pins 8.3.30 and 8.4.19) |
| Laravel (optional) | 12.x / Testbench 10 |
| Pest (optional) | 3.x on PHP 8.3/8.4, or 4.x on PHP 8.4; CI pins 3.8.6 and 4.7.8 |
| PHPUnit | 11.5.x (Pest 3) or 12.x (Pest 4 / PHP 8.4) |
| Renderer runtime | Node **24.21.0**, npm 11; permission to start a local Node process |
| Optional generators | Spatie Laravel PDF 1.9, Laravel-Dompdf 3.1 |

CI runs Laravel 12 with PHP 8.3/Pest 3, PHP 8.4/Pest 3 and PHP 8.4/Pest 4, plus PHP 8.3/Pest 3 without either generator. Standalone PHPUnit-only installation is checked too. Composer allows newer PHP versions, but they and other Laravel majors are not verified support targets for this release. Snapshot compatibility is narrower than PHP compatibility: use the same pinned Node/OS/architecture to create and compare baselines.

## Requirements and installation

Install as a **dev dependency** in an existing Laravel 12 app with Pest or PHPUnit. PHP needs the normal extensions required by your test runner (including DOM, mbstring, XML and XMLWriter). Check `php -v`, `node --version` and `npm --version` first.

### Install

```sh
composer require --dev 'craigsimps/assertpdf:^0.1' -W
vendor/bin/assertpdf install
```

### Developing against a local checkout

To test unreleased changes, clone this repository into `assertpdf` next to your application and point Composer at it. From your **application directory** (adjust the relative path if needed):

```sh
composer config repositories.assertpdf '{"type":"path","url":"../assertpdf","options":{"symlink":false}}'
composer require --dev 'craigsimps/assertpdf:*@dev' -W
vendor/bin/assertpdf install
```

To return to the published package, remove the local repository with `composer config --unset repositories.assertpdf` and require `^0.1` again. The supported installer runs `npm ci --ignore-scripts` against the package's lockfile **inside this package**, not in your application's node_modules. Re-run it after a Composer reinstall or renderer update. Installation requires npm/network access and a writable package directory; assertions do not install anything or access the network. A missing renderer gives an error naming this command. Composer does not run npm automatically.

For an existing Pest installation, no registration step is needed. A new Laravel skeleton normally includes PHPUnit, not Pest. On PHP 8.3/8.4, add Pest 3 with `composer require --dev 'pestphp/pest:^3.8' -W`, then `vendor/bin/pest --init`. On PHP 8.4, Pest 4 (`^4.7`) is an alternative; it requires PHPUnit 12. Accept the Pest Composer plugin only if you trust it. Without Pest, use the [PHPUnit equivalents](#phpunit-equivalents) and your existing test runner.

In Laravel, the auto-discovered provider also exposes `php artisan assertpdf:install` as an alias. If package discovery is disabled, register `AssertPdf\AssertPdfServiceProvider` in your app's providers manually, or use the standalone command. Tested integration: Laravel 12 / Testbench 10, Spatie Laravel PDF 1.9 and Laravel-Dompdf 3.1.

## Pest: text, pages and a visual snapshot

Save this in `tests/Feature/InvoicePdfTest.php`, pointing at a PDF your app generates. The first normal run fails until you explicitly create the baseline (see [snapshot updates](#explicit-snapshots-and-one-mask)):

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

## Demo

[craigsimps/assertpdf-demo](https://github.com/craigsimps/assertpdf-demo) is a small Laravel app with a Dompdf invoice endpoint and passing PDF tests. Its README covers setup and the text, page-count and snapshot tests. The landing site and brand pack live in [craigsimps/assertpdf-com](https://github.com/craigsimps/assertpdf-com).

## Laravel responses

All three expectations and their PHPUnit equivalents accept Laravel `TestResponse`, `Illuminate\Http\Response`, and Symfony `BinaryFileResponse` / `StreamedResponse` directly:

```php
expect($this->get('/invoices/1001/pdf'))
    ->toHavePdfText('Invoice 1001')
    ->toHavePdfPageCount(1)
    ->toMatchPdfSnapshot('invoice-1001');

expect(response($pdfBytes, 200, ['Content-Type' => 'application/pdf']))
    ->toHavePdfText('Invoice 1001');
expect(response()->file($pdfPath, ['Content-Type' => 'application/pdf']))
    ->toHavePdfPageCount(1);
expect(response()->streamDownload(fn () => print($pdfBytes), 'invoice.pdf', ['Content-Type' => 'application/pdf']))
    ->toHavePdfText('Invoice 1001');
```

Responses must have a successful 2xx status, `Content-Type: application/pdf` (parameters allowed), and PDF bytes. Invalid responses raise a runtime error including status and content type, even under Pest `->not`. Binary-file assertions read the local file without sending/deleting it. Stream assertions execute the callback with buffered output; callbacks may therefore have application side effects. Direct stream responses are cloned for each assertion; Laravel test responses cache their streamed content. Do not send a stream before asserting it.

## Optional PDF generators

Neither generator is installed for consumers by this package. Install/configure your chosen generator normally, then pass its object directly to any expectation:

```php
// spatie/laravel-pdf: uses PdfBuilder::base64() to generate the real PDF.
$pdf = \Spatie\LaravelPdf\Facades\Pdf::view('invoices.show', ['invoice' => $invoice]);
expect($pdf)->toHavePdfText('Invoice 1001')->toHavePdfPageCount(1);

// barryvdh/laravel-dompdf: uses PDF::output().
$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.show', ['invoice' => $invoice]);
expect($pdf)->toHavePdfText('Invoice 1001')->toMatchPdfSnapshot('dompdf-invoice');
```

The toolkit does not install Chrome/Puppeteer for Spatie or change generator settings. Your generator must already be able to render locally; each chained assertion can generate again. These assertions inspect bytes, not `Pdf::fake()` calls.

## Optional Laravel configuration

With the provider loaded, relative snapshot names are resolved beneath `tests/pdf-snapshots`. Absolute directories retain their existing meaning. Without Laravel/provider configuration, relative paths remain relative to the working directory and the pixel allowance stays zero.

```sh
php artisan vendor:publish --tag=assertpdf-config
```

`config/assertpdf.php`:

```php
return [
    'snapshot_path' => base_path('tests/pdf-snapshots'),
    'max_diff_pixels' => 0,
];
```

An explicit `maxDiffPixels` argument overrides configuration, including an explicit zero; omitted/null uses the configured default. Both Pest and PHPUnit use the same configuration.

## Explicit snapshots and one mask

Missing baselines **fail**. Create or replace them intentionally:

```sh
ASSERTPDF_UPDATE_SNAPSHOTS=1 vendor/bin/pest --filter='renders the invoice correctly'
vendor/bin/pest --filter='renders the invoice correctly'
```

Review the PNGs before accepting an update. Commit `manifest.json` and `page-*.png`; ignore the snapshot directory's `artifacts/` subdirectory. Never set the update flag in normal CI. Use one unique directory per snapshot and avoid concurrent writes to the same directory.

Updates are transactional per snapshot directory: every page is rendered and validated, then the complete baseline is staged in a sibling directory and renamed into place. A later-page or staging-write error leaves the previous baseline unchanged (or no baseline if it did not exist). The previous directory is restored if the final rename fails. Successful updates replace the whole snapshot directory, including removing stale pages/artifacts; do not store unrelated files there. This is not a concurrency lock or crash-recovery guarantee across the two directory renames; do not read/write the same snapshot concurrently.

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
use AssertPdf\PdfAssertions;

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
php bin/assertpdf install
composer validate --strict
composer lint
composer test
sh tests/installation.sh
```

Fixtures include a one-page invoice, two pages, a moved block, changed timestamps, changed dimensions and a deliberately corrupt object tree. `npm run fixtures` regenerates them deterministically.

The installation smoke creates disposable standalone and Testbench consumers: it checks the first-run error, the Composer binary from outside the package directory, provider auto-discovery, the Artisan alias, and an HTTP-response assertion. It needs network access for installation and removes its temporary apps afterward. The main suite covers each input's successful assertions and mismatches. Dompdf generates a real PDF; the Spatie serialization test supplies fixture bytes at the Browsershot boundary so CI needs no Chrome.

The same Linux/amd64 checks used by `.github/workflows/tests.yml` can run locally:

```sh
docker build --platform linux/amd64 -f Dockerfile.ci -t assertpdf-ci .
docker run --rm --network none assertpdf-ci
# Repeat without either optional generator installed:
docker build --platform linux/amd64 --build-arg OPTIONAL_GENERATORS=0 -f Dockerfile.ci -t assertpdf-ci-minimal .
docker run --rm --network none assertpdf-ci-minimal
```

The images pin PHP 8.3.30 / 8.4.19, Node 24.21.0 and Composer 2.8.9 by image digest on Debian Bookworm. To reproduce another matrix row, pass its `PHP_IMAGE` and `PEST_VERSION` build arguments from [the workflow](.github/workflows/tests.yml). Pest 4 is resolved in the image with a PHP 8.4 Composer platform, without changing the toolkit's committed Pest 3 lockfile. Generator absence skips only the two generator-specific tests. Installation checks run during the networked image build; the full assertion suites run with networking disabled. A local container run is **not** a hosted GitHub Actions run.

## Release and feedback

See [CHANGELOG.md](CHANGELOG.md) for the v0.1.0 contents and [release readiness](docs/launch/release-readiness.md) for identity, licence review and the publication gates. During 0.x, minor releases may contain breaking changes; patch releases are fixes. Pin `^0.1` to stay within the first minor line, and review snapshots explicitly after any renderer update.

After public launch, report a small reproducible PDF/test and runtime versions in [GitHub issues](https://github.com/craigsimps/assertpdf/issues), using synthetic data rather than customer invoices. No telemetry is collected by the toolkit. Release, demo publication and landing-page go-live still require Craig's approval.
