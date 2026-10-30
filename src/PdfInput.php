<?php

declare(strict_types=1);

namespace AssertPdf;

use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Normalize supported inputs without requiring Laravel or a PDF generator to be installed. */
final class PdfInput
{
    public static function bytes(mixed $pdf): string
    {
        if ($pdf instanceof \Spatie\LaravelPdf\PdfBuilder) {
            $pdf = base64_decode($pdf->base64(), strict: true);
        } elseif ($pdf instanceof \Barryvdh\DomPDF\PDF) {
            $pdf = $pdf->output();
        }

        $testResponse = $pdf instanceof TestResponse ? $pdf : null;
        $response = $testResponse ? $testResponse->baseResponse : $pdf;
        if ($response instanceof Response) {
            $type = $response->headers->get('Content-Type', '(missing)');
            if (!$response->isSuccessful() || strtolower(trim(explode(';', $type)[0])) !== 'application/pdf') {
                throw new \RuntimeException('Expected a successful response with Content-Type application/pdf.');
            }
            if ($response instanceof BinaryFileResponse) {
                $pdf = file_get_contents($response->getFile()->getPathname());
            } elseif ($response instanceof StreamedResponse) {
                $level = ob_get_level();
                $pdf = '';
                ob_start(static function (string $chunk) use (&$pdf): string {
                    $pdf .= $chunk;
                    return '';
                });
                try {
                    if ($testResponse) {
                        $pdf = $testResponse->streamedContent();
                    } else {
                        // Do not mark the original response as sent; assertions may be chained.
                        (clone $response)->sendContent();
                    }
                } finally {
                    while (ob_get_level() > $level) ob_end_clean();
                }
            } else {
                $pdf = $response->getContent();
            }
            if (!is_string($pdf) || !str_starts_with($pdf, '%PDF-')) {
                throw new \RuntimeException('Body does not begin with %PDF-.');
            }
            return $pdf;
        }

        if (!is_string($pdf)) {
            throw new \RuntimeException('Provide PDF bytes, a local path, a PDF response, a Spatie PdfBuilder or a Dompdf PDF object.');
        }
        if (!str_starts_with($pdf, '%PDF-')) {
            if (str_contains($pdf, '://') || str_contains($pdf, "\0") || !is_file($pdf) || !is_readable($pdf)) {
                throw new \RuntimeException('Provide PDF bytes beginning with %PDF- or a readable local file path (not a URL).');
            }
            $pdf = file_get_contents($pdf);
        }
        return $pdf;
    }
}
