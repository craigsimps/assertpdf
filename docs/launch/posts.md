# Launch drafts — Craig sends, nothing posted

Prepared 2026-10-30 with launch URLs filled in. **Send only after the repositories are public and the package is on Packagist.** Check each destination's current submission rules before sending; no account, submission or contact is part of this assignment.

## Laravel News tip

**Subject:** AssertPDF: a free PDF testing toolkit for Laravel

Hi Laravel News team,

I've released AssertPDF, a free MIT-licensed Composer dev package for Laravel. It lets you check the PDF your app generates in Pest or PHPUnit. You can test for a missing invoice total or an extra page, then compare rendered snapshots to catch layout changes.

```php
expect($this->get('/invoices/1001/pdf'))
    ->toHavePdfText('Invoice 1001')
    ->toHavePdfText('120.00')
    ->toHavePdfPageCount(1);
```

You can pass it a Laravel response, a local PDF file or PDF bytes. It also accepts Spatie Laravel PDF and Dompdf objects. The renderer needs Node 24.21.0; assertions run offline after installation, without a browser or hosted service. You create and review snapshot baselines explicitly, using the same renderer environment as CI.

Docs/source: https://github.com/craigsimps/assertpdf
Package: https://packagist.org/packages/craigsimps/assertpdf
Minimal invoice demo and tests: https://github.com/craigsimps/assertpdf-demo

Thanks,
Craig

## r/laravel

**Title:** I built AssertPDF to test the PDFs my Laravel app generates

I wanted a test to fail if an invoice total disappeared from a PDF or the invoice spilled onto an extra page. A generator fake can pass in either case. I've built AssertPDF to check the resulting PDF in Pest or PHPUnit. It's a free MIT-licensed dev package.

```php
expect($this->get('/invoices/1001/pdf'))
    ->toHavePdfText('120.00')
    ->toHavePdfPageCount(1);
```

For layout checks, `->toMatchPdfSnapshot('invoice-1001')` compares rendered pages with a baseline you create and review. A mismatch writes actual and diff PNGs. It supports Laravel 12, PHP 8.3/8.4 and compatible Pest 3/4 or PHPUnit 11.5/12. It needs a pinned Node renderer and runs locally. It does not use OCR; create and compare snapshots on the same operating system and architecture.

Source/install: https://github.com/craigsimps/assertpdf · Package: https://packagist.org/packages/craigsimps/assertpdf · Runnable invoice demo: https://github.com/craigsimps/assertpdf-demo

I'd like to hear which PDF regressions you need to catch. If you try it and hit an installation or assertion problem, a small reproducible example would help. Please use synthetic invoices rather than customer data. Keep generator fakes for testing that your app requested a PDF; AssertPDF checks what came back.

## X

I've released AssertPDF, a free MIT-licensed dev package for Laravel. Check PDF text and page counts in Pest or PHPUnit, or compare rendered snapshots. Assertions run offline with a local Node renderer.

Docs + invoice demo: https://github.com/craigsimps/assertpdf

Before sending, check that the text and link fit the account's character limit.
