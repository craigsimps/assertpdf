<?php

declare(strict_types=1);

namespace PdfTesting;

use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;
use Throwable;

final class PdfAssertions
{
    public static function assertPdfContainsText(string $pdf, string $text): void
    {
        $actual = self::run($pdf, 'inspect')['text'];
        Assert::assertStringContainsString($text, $actual, "Expected PDF to contain \"{$text}\".\nExtracted text:\n{$actual}");
    }

    public static function assertPdfPageCount(string $pdf, int $count): void
    {
        $actual = self::run($pdf, 'inspect')['pages'];
        Assert::assertSame($count, $actual, "Expected PDF to have {$count} page(s); found {$actual}.");
    }

    /** Mask coordinates are integer pixels at 72 DPI, from the top-left, with a 1-based page. */
    public static function assertPdfMatchesSnapshot(string $pdf, string $directory, ?array $mask = null, int $maxDiffPixels = 0): void
    {
        $result = self::run($pdf, 'snapshot', [
            'directory' => $directory,
            'mask' => $mask,
            'maxDiffPixels' => $maxDiffPixels,
            'update' => getenv('PDF_TESTING_UPDATE_SNAPSHOTS') === '1',
        ]);
        Assert::assertTrue($result['matches'], $result['message']);
    }

    private static function run(string $pdf, string $operation, array $options = []): array
    {
        try {
            if (!str_starts_with($pdf, '%PDF-')) {
                if (str_contains($pdf, '://') || str_contains($pdf, "\0") || !is_file($pdf) || !is_readable($pdf)) {
                    throw new \RuntimeException('Provide PDF bytes beginning with %PDF- or a readable local file path (not a URL).');
                }
                $pdf = file_get_contents($pdf);
            }

            $process = new Process(['node', dirname(__DIR__).'/bin/pdf.mjs']);
            $process->setInput(json_encode(['pdf' => base64_encode($pdf), 'operation' => $operation] + $options, JSON_THROW_ON_ERROR));
            $process->setTimeout(60);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException(trim($process->getErrorOutput()));
            }
            return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $error) {
            // An unreadable input is an error, not a mismatch that Pest's ->not may invert.
            throw new \RuntimeException('Cannot read PDF: '.$error->getMessage(), previous: $error);
        }
    }
}
