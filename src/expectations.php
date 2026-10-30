<?php

declare(strict_types=1);

use PdfTesting\PdfAssertions;
use Pest\Expectation;

// Autoload the class rather than depending on Composer's order for Pest's function files.
if (class_exists(Expectation::class)) {
    $expectation = new Expectation(null);
    $expectation->extend('toHavePdfText', function (string $text) {
        PdfAssertions::assertPdfContainsText($this->value, $text);
        return $this;
    });
    $expectation->extend('toHavePdfPageCount', function (int $count) {
        PdfAssertions::assertPdfPageCount($this->value, $count);
        return $this;
    });
    $expectation->extend('toMatchPdfSnapshot', function (string $directory, ?array $mask = null, int $maxDiffPixels = 0) {
        PdfAssertions::assertPdfMatchesSnapshot($this->value, $directory, $mask, $maxDiffPixels);
        return $this;
    });
}
