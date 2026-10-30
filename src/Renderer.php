<?php

declare(strict_types=1);

namespace PdfTesting;

use Symfony\Component\Process\Process;

final class Renderer
{
    public static function install(?callable $output = null): int
    {
        $process = new Process(['npm', 'ci', '--ignore-scripts', '--no-audit', '--no-fund'], dirname(__DIR__));
        $process->setTimeout(300);
        return $process->run($output);
    }

    public static function assertInstalled(): void
    {
        foreach (['pdfjs-dist', '@napi-rs/canvas', 'pixelmatch', 'pngjs'] as $package) {
            if (!is_file(dirname(__DIR__).'/node_modules/'.$package.'/package.json')) {
                throw new \RuntimeException('PDF renderer is not installed. Run vendor/bin/pdf-testing install (or php artisan pdf-testing:install).');
            }
        }
    }
}
