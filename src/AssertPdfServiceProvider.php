<?php

declare(strict_types=1);

namespace AssertPdf;

use Illuminate\Support\ServiceProvider;

final class AssertPdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/assertpdf.php', 'assertpdf');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallRendererCommand::class]);
            $this->publishes([dirname(__DIR__).'/config/assertpdf.php' => config_path('assertpdf.php')], 'assertpdf-config');
        }
    }
}
