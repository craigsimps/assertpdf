# Launch drafts — Craig sends, nothing posted

Prepared 2026-10-30. **Do not send before public release approval.** Replace `[PACKAGE_URL]`, `[REPO_URL]` and `[DEMO_URL]` with verified public links. Expected package/repo URLs are `https://packagist.org/packages/craigsimps/laravel-pdf-testing` and `https://github.com/craigsimps/laravel-pdf-testing`; neither public availability nor Packagist publication is asserted today. Demo URL remains a TODO. Check each destination's current submission rules before sending; no account, submission or contact is part of this assignment.

## Laravel News tip

**Subject:** Free Laravel PDF testing assertions for the actual generated document

Hi Laravel News team,

I've released a small MIT-licensed Composer dev package for testing real PDFs in Pest or PHPUnit. It checks extracted text, page count and rendered visual snapshots, so an invoice test can catch a missing total or an unexpected page break that a PDF facade fake won't see.

```php
expect($this->get('/invoices/1001/pdf'))
    ->toHavePdfText('Invoice 1001')
    ->toHavePdfText('120.00')
    ->toHavePdfPageCount(1);
```

It accepts Laravel responses, local files and bytes, with optional Spatie Laravel PDF/Dompdf object inputs. The toolkit renderer needs Node 24.21.0, but no browser or hosted service; assertions run offline after installation. Visual baselines must be explicitly created/reviewed on the same renderer environment used in CI.

Docs/source: [REPO_URL]
Package: [PACKAGE_URL]
Minimal invoice demo and tests: [DEMO_URL]

Thanks,
Craig

## r/laravel

**Title:** I built a free Pest/PHPUnit toolkit to test real invoice PDFs, not just facade calls

I wanted a test to fail when the generated PDF lost an invoice total or gained an extra page, even though the generator was still called correctly. This is a small MIT-licensed dev package with text, page-count and opt-in visual-snapshot assertions.

```php
expect($this->get('/invoices/1001/pdf'))
    ->toHavePdfText('120.00')
    ->toHavePdfPageCount(1);
```

After installation, `->toMatchPdfSnapshot('invoice-1001')` can also compare rendered pages. Baseline updates are explicit, with actual/diff PNGs on failure. It supports Laravel 12, PHP 8.3/8.4 and compatible Pest 3/4 or PHPUnit 11.5/12. A pinned Node renderer is required; no toolkit-hosted service, OCR or promise of cross-OS pixel-identical snapshots.

Source/install: [REPO_URL] · Package: [PACKAGE_URL] · Runnable invoice demo: [DEMO_URL]

I'm the author. What PDF regression would you most want a test to catch? Reproducible install/correctness reports are particularly useful; please use synthetic invoices, not customer data. This complements generator fakes rather than replacing them.

## X

Free MIT dev package: test the PDF Laravel actually emits.

Pest/PHPUnit assertions for text, page count and opt-in visual snapshots. Local Node renderer; offline assertions. Complements PDF fakes.

Docs + invoice demo: [REPO_URL]

Before sending, check the final text/link length fits the account's limit. No claims about users, adoption or performance have been invented.
