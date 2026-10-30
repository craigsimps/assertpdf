# Landing-page content brief (design handoff only)

Part 1 of [milestone 3](https://github.com/craigsimps/assertpdf/issues/3) supplies content, not a layout or visual identity. Claude Code produces the brand identity pack first, then designs the one-page site and any demo styling. Nothing goes public without Craig's approval.

**Problem / lead:** Your PDF fake passed. Did the invoice still contain the total? Test the PDF your Laravel app actually emits: text, page count and rendered visual snapshots. A free MIT-licensed Composer dev package, complementary to generator fakes.

**Code sample:**

```php
it('renders the invoice correctly', function () {
    expect($this->get('/invoices/1001/pdf'))
        ->toHavePdfText('Invoice 1001')
        ->toHavePdfText('120.00')
        ->toHavePdfPageCount(1);
});
```

**Install (only after Packagist publication):**

```sh
composer require --dev 'craigsimps/assertpdf:^0.1' -W
vendor/bin/assertpdf install
```

Prerequisites: PHP 8.3/8.4, Laravel 12 if using Laravel, compatible Pest 3/4 or PHPUnit 11.5/12, Node 24.21.0/npm 11. The second command installs the pinned renderer inside the package. Network needed for install, not assertions. No browser required by the toolkit. Mention that visual snapshots require an explicit baseline update on the same pinned renderer/OS/architecture; this is not OCR.

**Links:** Docs/source: `https://github.com/craigsimps/assertpdf` (private today). Demo: **TODO public placeholder** `https://github.com/craigsimps/assertpdf-demo`; no remote exists. Review app is locally at `../laravel-pdf-testing-demo`, with an actual invoice PDF route and passing package tests. Replace placeholders with verified public links before launch. No screenshot, public hosting, analytics, paid tier or signup is requested here.
