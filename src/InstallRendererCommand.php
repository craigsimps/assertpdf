<?php

declare(strict_types=1);

namespace PdfTesting;

use Illuminate\Console\Command;

final class InstallRendererCommand extends Command
{
    protected $signature = 'pdf-testing:install';
    protected $description = 'Install the pinned PDF testing renderer inside the package';

    public function handle(): int
    {
        return Renderer::install(fn (string $type, string $buffer) => $this->output->write($buffer));
    }
}
