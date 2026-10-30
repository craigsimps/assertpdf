<?php

declare(strict_types=1);

namespace PdfTesting;

use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;
use Throwable;

final class PdfAssertions
{
    public static function assertPdfContainsText(mixed $pdf, string $text): void
    {
        $actual = self::run($pdf, 'inspect')['text'];
        Assert::assertStringContainsString($text, $actual, "Expected PDF to contain \"{$text}\".\nExtracted text:\n{$actual}");
    }

    public static function assertPdfPageCount(mixed $pdf, int $count): void
    {
        $actual = self::run($pdf, 'inspect')['pages'];
        Assert::assertSame($count, $actual, "Expected PDF to have {$count} page(s); found {$actual}.");
    }

    /** Mask coordinates are integer pixels at 72 DPI, from the top-left, with a 1-based page. */
    public static function assertPdfMatchesSnapshot(mixed $pdf, string $directory, ?array $mask = null, ?int $maxDiffPixels = null): void
    {
        $config = class_exists(\Illuminate\Container\Container::class) && \Illuminate\Container\Container::getInstance()->bound('config')
            ? \Illuminate\Container\Container::getInstance()->make('config')->get('pdf-testing', []) : [];
        if ($directory !== '' && isset($config['snapshot_path']) && !str_starts_with($directory, '/') && !preg_match('/^[A-Za-z]:[\\\\\/]/', $directory) && !str_contains($directory, '://')) {
            $directory = rtrim($config['snapshot_path'], '/\\').'/'.$directory;
        }
        $result = self::run($pdf, 'snapshot', [
            'directory' => $directory,
            'mask' => $mask,
            'maxDiffPixels' => $maxDiffPixels ?? $config['max_diff_pixels'] ?? 0,
            'update' => getenv('PDF_TESTING_UPDATE_SNAPSHOTS') === '1',
        ]);
        Assert::assertTrue($result['matches'], $result['message']);
    }

    private static function run(mixed $pdf, string $operation, array $options = []): array
    {
        try {
            $bytes = PdfInput::bytes($pdf);
            Renderer::assertInstalled();
            $process = new Process(['node', dirname(__DIR__).'/bin/pdf.mjs']);
            $process->setInput(json_encode(['pdf' => base64_encode($bytes), 'operation' => $operation] + $options, JSON_THROW_ON_ERROR));
            $process->setTimeout(60);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException(trim($process->getErrorOutput()));
            }
            return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $error) {
            $response = $pdf instanceof \Illuminate\Testing\TestResponse ? $pdf->baseResponse : $pdf;
            $context = $response instanceof \Symfony\Component\HttpFoundation\Response
                ? ' (status '.$response->getStatusCode().', content type '.$response->headers->get('Content-Type', '(missing)').')' : '';
            // An unreadable input is an error, not a mismatch that Pest's ->not may invert.
            throw new \RuntimeException('Cannot read PDF: '.$error->getMessage().$context, previous: $error);
        }
    }
}
