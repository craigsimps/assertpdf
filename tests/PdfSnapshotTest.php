<?php

declare(strict_types=1);

use PdfTesting\PdfAssertions;
use PHPUnit\Framework\AssertionFailedError;

beforeEach(function () {
    $this->snapshot = __DIR__.'/tmp/'.bin2hex(random_bytes(8));
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS');
});

afterEach(function () {
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS');
    if (is_dir($this->snapshot)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->snapshot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->snapshot);
    }
});

function createPdfBaseline(string $directory, string $fixture = 'invoice'): void
{
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS=1');
    try {
        PdfAssertions::assertPdfMatchesSnapshot(__DIR__."/fixtures/{$fixture}.pdf", $directory);
    } finally {
        putenv('PDF_TESTING_UPDATE_SNAPSHOTS');
    }
}

it('never creates a baseline without explicit update permission', function () {
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/invoice.pdf', $this->snapshot))
        ->toThrow(AssertionFailedError::class, 'PDF_TESTING_UPDATE_SNAPSHOTS=1');
    expect(is_dir($this->snapshot))->toBeFalse();
});

it('rejects unreadable pages before baseline mismatches regardless of assertion polarity', function (string $baseline, string $assertion, string $fixture, string $kids, string $invalidKids) {
    if ($baseline === 'incompatible renderer') {
        createPdfBaseline($this->snapshot, $fixture);
        $manifestPath = $this->snapshot.'/manifest.json';
        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $manifest['renderer']['node'] = 'incompatible';
        file_put_contents($manifestPath, json_encode($manifest, JSON_THROW_ON_ERROR));
    }
    // Equal-length replacements keep xref offsets intact but point to a missing page.
    $pdf = str_replace($kids, $invalidKids, file_get_contents(__DIR__."/fixtures/{$fixture}.pdf"));
    expect(function () use ($assertion, $pdf) {
        match ($assertion) {
            'PHPUnit' => PdfAssertions::assertPdfMatchesSnapshot($pdf, $this->snapshot),
            'Pest' => expect($pdf)->toMatchPdfSnapshot($this->snapshot),
            'negated Pest' => expect($pdf)->not->toMatchPdfSnapshot($this->snapshot),
        };
    })->toThrow(RuntimeException::class, 'Cannot read PDF:');
})->with(['missing manifest', 'incompatible renderer'])
    ->with(['PHPUnit', 'Pest', 'negated Pest'])
    ->with([
        'first page' => ['invoice', '/Kids [4 0 R]', '/Kids [9 0 R]'],
        'second page' => ['two-pages', '/Kids [4 0 R 6 0 R]', '/Kids [4 0 R 9 0 R]'],
    ]);

it('creates an explicit baseline and compares identical PDF bytes', function () {
    createPdfBaseline($this->snapshot);
    PdfAssertions::assertPdfMatchesSnapshot(file_get_contents(__DIR__.'/fixtures/invoice.pdf'), $this->snapshot);
    expect(getimagesize($this->snapshot.'/page-1.png'))->toMatchArray([0 => 300, 1 => 300, 2 => IMAGETYPE_PNG]);
});

it('preserves the baseline and writes viewable actual and diff PNGs for a moved block', function () {
    createPdfBaseline($this->snapshot);
    $before = hash_file('sha256', $this->snapshot.'/page-1.png');
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/moved.pdf', $this->snapshot))
        ->toThrow(AssertionFailedError::class, 'diff-1.png');
    expect(hash_file('sha256', $this->snapshot.'/page-1.png'))->toBe($before);
    expect(getimagesize($this->snapshot.'/artifacts/diff-1.png'))->toMatchArray([0 => 300, 1 => 300, 2 => IMAGETYPE_PNG]);
    expect(hash_file('sha256', $this->snapshot.'/artifacts/actual-1.png'))->not->toBe($before);
});

it('ignores only the selected timestamp region on the selected page', function () {
    createPdfBaseline($this->snapshot);
    $mask = ['page' => 1, 'x' => 20, 'y' => 50, 'width' => 100, 'height' => 25];
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/timestamp.pdf', $this->snapshot))
        ->toThrow(AssertionFailedError::class, 'diff-1.png');
    PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/timestamp.pdf', $this->snapshot, $mask);
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/moved.pdf', $this->snapshot, $mask))
        ->toThrow(AssertionFailedError::class, 'diff-1.png');
    createPdfBaseline($this->snapshot, 'two-pages');
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/two-timestamps.pdf', $this->snapshot, $mask))
        ->toThrow(AssertionFailedError::class, 'diff-2.png');
});

it('fails on page count and dimensions even with a generous pixel allowance', function () {
    createPdfBaseline($this->snapshot);
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/two-pages.pdf', $this->snapshot))
        ->toThrow(AssertionFailedError::class, 'page count');
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/resized.pdf', $this->snapshot, maxDiffPixels: 1000000))
        ->toThrow(AssertionFailedError::class, 'dimensions');
});

it('honours the changed-pixel allowance without accepting the next pixel', function () {
    createPdfBaseline($this->snapshot);
    // The solid blue rectangle moves 75px; 2 × 75 × 40 = 6000 changed pixels.
    PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/moved.pdf', $this->snapshot, maxDiffPixels: 6000);
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/moved.pdf', $this->snapshot, maxDiffPixels: 5999))
        ->toThrow(AssertionFailedError::class, '6000 changed pixels');
});

it('rejects an out-of-bounds mask instead of silently hiding a mistake', function () {
    createPdfBaseline($this->snapshot);
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/invoice.pdf', $this->snapshot,
        ['page' => 1, 'x' => 290, 'y' => 0, 'width' => 30, 'height' => 20]))
        ->toThrow(RuntimeException::class, 'Mask');
});

it('leaves the entire baseline untouched when an update fails on a later page', function (bool $existing) {
    if ($existing) {
        createPdfBaseline($this->snapshot);
    }
    $before = $existing ? [file_get_contents($this->snapshot.'/manifest.json'), file_get_contents($this->snapshot.'/page-1.png')] : null;
    // First page renders differently, second page refers to a nonexistent object.
    $pdf = str_replace(['/Kids [4 0 R 6 0 R]', 'Invoice 1001'], ['/Kids [4 0 R 9 0 R]', 'Invoice 9999'],
        file_get_contents(__DIR__.'/fixtures/two-pages.pdf'));
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS=1');
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot($pdf, $this->snapshot))
        ->toThrow(RuntimeException::class, 'Cannot read PDF:');
    if ($existing) {
        expect([file_get_contents($this->snapshot.'/manifest.json'), file_get_contents($this->snapshot.'/page-1.png')])->toBe($before);
        expect(file_exists($this->snapshot.'/page-2.png'))->toBeFalse();
    } else {
        expect(is_dir($this->snapshot))->toBeFalse();
    }
    expect(glob($this->snapshot.'.*'))->toBe([]);
})->with([true, false]);

it('replaces all pages and removes stale pages on a successful update', function () {
    createPdfBaseline($this->snapshot, 'two-pages');
    createPdfBaseline($this->snapshot, 'moved');
    expect(file_exists($this->snapshot.'/page-2.png'))->toBeFalse();
    PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/moved.pdf', $this->snapshot);
    expect(glob($this->snapshot.'.*'))->toBe([]);
});
