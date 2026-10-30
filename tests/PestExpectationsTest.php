<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;

it('chains real PDF text and page expectations including their opposites', function () {
    expect(__DIR__.'/fixtures/invoice.pdf')
        ->toHavePdfText('Invoice 1001')
        ->toHavePdfPageCount(1)
        ->not->toHavePdfText('Invoice 9999')
        ->not->toHavePdfPageCount(2);
});

it('exposes useful failures through Pest text and page expectations', function () {
    expect(fn () => expect(__DIR__.'/fixtures/invoice.pdf')->toHavePdfText('Missing total'))
        ->toThrow(AssertionFailedError::class, 'Extracted text:');
    expect(fn () => expect(__DIR__.'/fixtures/two-pages.pdf')->toHavePdfPageCount(1))
        ->toThrow(AssertionFailedError::class, 'found 2');
});

it('does not treat an unreadable PDF as a successful negative expectation', function () {
    expect(fn () => expect(__DIR__.'/fixtures/broken.pdf')->not->toHavePdfText('Invoice 1001'))
        ->toThrow(RuntimeException::class, 'Cannot read PDF:');
});

it('exposes the snapshot expectation with update, match, mask and failure', function () {
    $directory = __DIR__.'/tmp/pest-snapshot';
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS=1');
    try {
        expect(__DIR__.'/fixtures/invoice.pdf')->toMatchPdfSnapshot($directory);
    } finally {
        putenv('PDF_TESTING_UPDATE_SNAPSHOTS');
    }
    try {
        expect(__DIR__.'/fixtures/invoice.pdf')->toMatchPdfSnapshot($directory);
        expect(__DIR__.'/fixtures/timestamp.pdf')->toMatchPdfSnapshot($directory,
            ['page' => 1, 'x' => 20, 'y' => 50, 'width' => 100, 'height' => 25]);
        expect(fn () => expect(__DIR__.'/fixtures/moved.pdf')->toMatchPdfSnapshot($directory))
            ->toThrow(AssertionFailedError::class, 'diff-1.png');
    } finally {
        if (is_dir($directory)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($directory);
        }
    }
});
