<?php

declare(strict_types=1);

use PdfTesting\PdfAssertions;
use PHPUnit\Framework\AssertionFailedError;

it('reads text and page count from real PDF paths and bytes', function () {
    PdfAssertions::assertPdfContainsText(__DIR__.'/fixtures/invoice.pdf', 'Invoice 1001');
    PdfAssertions::assertPdfContainsText(file_get_contents(__DIR__.'/fixtures/invoice.pdf'), 'Issued 10:30');
    PdfAssertions::assertPdfPageCount(__DIR__.'/fixtures/invoice.pdf', 1);
    PdfAssertions::assertPdfPageCount(__DIR__.'/fixtures/two-pages.pdf', 2);
});

it('reports extracted text when expected text is absent', function () {
    try {
        PdfAssertions::assertPdfContainsText(__DIR__.'/fixtures/invoice.pdf', 'Invoice 9999');
        test()->fail('The absent text must fail.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('Invoice 9999', 'Extracted text:', 'Invoice 1001', 'Issued 10:30');
    }
});

it('reports the actual page count', function () {
    try {
        PdfAssertions::assertPdfPageCount(__DIR__.'/fixtures/two-pages.pdf', 1);
        test()->fail('Two pages must not pass a one-page assertion.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('Expected PDF to have 1 page(s); found 2');
    }
});

it('rejects broken PDFs and nonlocal inputs clearly', function (string $input) {
    expect(fn () => PdfAssertions::assertPdfPageCount($input, 1))
        ->toThrow(RuntimeException::class, 'Cannot read PDF:');
})->with([
    'broken object tree' => __DIR__.'/fixtures/broken.pdf',
    'missing path' => __DIR__.'/fixtures/missing.pdf',
    'URL is not fetched' => 'https://example.invalid/invoice.pdf',
    'empty bytes' => '',
]);
