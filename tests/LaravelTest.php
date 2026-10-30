<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use PdfTesting\PdfAssertions;
use PdfTesting\PdfTestingServiceProvider;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(Orchestra\Testbench\TestCase::class);

beforeEach(function () {
    $this->app->register(PdfTestingServiceProvider::class);
    $this->snapshot = __DIR__.'/tmp/laravel-'.bin2hex(random_bytes(6));
    $this->app['config']->set('pdf-testing.snapshot_path', $this->snapshot);
});

afterEach(function () {
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS');
    (new Illuminate\Filesystem\Filesystem)->deleteDirectory($this->snapshot);
});

function laravelPdfInput(string $type): mixed
{
    $bytes = file_get_contents(__DIR__.'/fixtures/invoice.pdf');
    return match ($type) {
        'response' => new Response($bytes, 200, ['Content-Type' => 'application/pdf']),
        'test response' => TestResponse::fromBaseResponse(new Response($bytes, 200, ['Content-Type' => 'application/pdf'])),
        'binary file' => new BinaryFileResponse(__DIR__.'/fixtures/invoice.pdf', 200, ['Content-Type' => 'application/pdf']),
        'stream' => new StreamedResponse(static function () use ($bytes) { echo $bytes; }, 200, ['Content-Type' => 'application/pdf']),
        'test stream' => TestResponse::fromBaseResponse(new StreamedResponse(static function () use ($bytes) { echo $bytes; }, 200, ['Content-Type' => 'application/pdf'])),
        'dompdf' => app('dompdf.wrapper')->loadHTML('<h1>Invoice 1001</h1>'),
        // Isolate Chrome generation, not PdfBuilder serialization or the PDF assertions.
        'spatie' => new class($bytes) extends Spatie\LaravelPdf\PdfBuilder {
            public function __construct(private string $bytes) {}
            public function getBrowsershot(): Spatie\Browsershot\Browsershot
            {
                return new class($this->bytes) extends Spatie\Browsershot\Browsershot {
                    public function __construct(private string $bytes) {}
                    public function base64pdf(): string { return base64_encode($this->bytes); }
                };
            }
        },
    };
}

it('asserts real PDF contents from Laravel and optional generator inputs', function (string $type) {
    if ($type === 'dompdf') {
        if (!class_exists(Barryvdh\DomPDF\ServiceProvider::class)) $this->markTestSkipped('Optional Dompdf is not installed.');
        $this->app->register(Barryvdh\DomPDF\ServiceProvider::class);
    }
    if ($type === 'spatie' && !class_exists(Spatie\LaravelPdf\PdfBuilder::class)) $this->markTestSkipped('Optional Spatie PDF is not installed.');
    $pdf = laravelPdfInput($type);
    expect($pdf)->toHavePdfText('Invoice 1001')->toHavePdfPageCount(1);
    expect(fn () => expect($pdf)->toHavePdfText('Invoice 9999'))->toThrow(AssertionFailedError::class, 'Invoice 1001');
    expect(fn () => PdfAssertions::assertPdfPageCount($pdf, 2))->toThrow(AssertionFailedError::class, 'found 1');
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS=1');
    expect($pdf)->toMatchPdfSnapshot('invoice');
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS');
    expect($pdf)->toMatchPdfSnapshot('invoice');
    expect(fn () => PdfAssertions::assertPdfMatchesSnapshot(__DIR__.'/fixtures/moved.pdf', 'invoice'))
        ->toThrow(AssertionFailedError::class);
    expect(is_file($this->snapshot.'/invoice/manifest.json'))->toBeTrue();
})->with(['response', 'test response', 'binary file', 'stream', 'test stream', 'dompdf', 'spatie']);

it('reports HTTP status and content type for invalid PDF responses even when negated', function (int $status, string $type, string $body) {
    $response = TestResponse::fromBaseResponse(new Response($body, $status, ['Content-Type' => $type]));
    foreach ([false, true] as $negate) {
        expect(function () use ($response, $negate) {
            $negate ? expect($response)->not->toHavePdfText('Invoice') : expect($response)->toHavePdfText('Invoice');
        })->toThrow(RuntimeException::class, "status {$status}, content type {$type}");
    }
})->with([
    [404, 'text/html', '<h1>Not found</h1>'],
    [500, 'application/pdf', '%PDF-invalid'],
    [200, 'application/json', '{"error":"no invoice"}'],
    [200, 'application/pdf', '<html>Not a PDF</html>'],
]);

it('cleans output buffers after a streaming failure', function () {
    $level = ob_get_level();
    $response = new StreamedResponse(function () { echo 'partial'; throw new RuntimeException('stream failed'); }, 200, ['Content-Type' => 'application/pdf']);
    expect(fn () => expect($response)->not->toHavePdfPageCount(1))->toThrow(RuntimeException::class, 'stream failed');
    expect(ob_get_level())->toBe($level);
});

it('uses configured pixel defaults but honours an explicit zero override', function () {
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS=1');
    expect(__DIR__.'/fixtures/invoice.pdf')->toMatchPdfSnapshot('invoice');
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS');
    $this->app['config']->set('pdf-testing.max_diff_pixels', 6000);
    expect(__DIR__.'/fixtures/moved.pdf')->toMatchPdfSnapshot('invoice');
    expect(fn () => expect(__DIR__.'/fixtures/moved.pdf')->toMatchPdfSnapshot('invoice', maxDiffPixels: 0))
        ->toThrow(AssertionFailedError::class, 'allowed 0');
});

it('publishes optional config and exposes the Artisan installer', function () {
    $this->artisan('list')->expectsOutputToContain('pdf-testing:install')->assertSuccessful();
    expect($this->app['config']->get('pdf-testing.max_diff_pixels'))->toBe(0);
    expect(PdfTestingServiceProvider::pathsToPublish(PdfTestingServiceProvider::class, 'pdf-testing-config'))
        ->toHaveCount(1);
});

it('accepts a response obtained through the Laravel HTTP test client', function () {
    $this->app['router']->get('/invoice', fn () => response()->file(__DIR__.'/fixtures/invoice.pdf', ['Content-Type' => 'application/pdf']));
    expect($this->get('/invoice'))->toHavePdfText('Invoice 1001')->toHavePdfPageCount(1);
    $this->app['router']->get('/invoice-error', fn () => response('No invoice', 404)->header('Content-Type', 'text/plain'));
    expect(fn () => expect($this->get('/invoice-error'))->not->toHavePdfText('Invoice'))
        ->toThrow(RuntimeException::class, 'status 404, content type text/plain');
});

it('does not update the snapshot root when the snapshot name is empty', function () {
    putenv('PDF_TESTING_UPDATE_SNAPSHOTS=1');
    expect(fn () => expect(__DIR__.'/fixtures/invoice.pdf')->toMatchPdfSnapshot(''))
        ->toThrow(RuntimeException::class, 'Snapshot directory must be a local path');
    expect(is_dir($this->snapshot))->toBeFalse();
});
