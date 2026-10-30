<?php

declare(strict_types=1);

namespace PdfTesting;

use Illuminate\Support\ServiceProvider;

final class PdfTestingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/pdf-testing.php', 'pdf-testing');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallRendererCommand::class]);
            $this->publishes([dirname(__DIR__).'/config/pdf-testing.php' => config_path('pdf-testing.php')], 'pdf-testing-config');
        }
    }
}
