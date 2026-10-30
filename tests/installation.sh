#!/bin/sh
# Networked install smoke; assertions themselves remain offline in the main CI suite.
set -eu
ROOT=$(pwd)
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/package" "$TMP/app"
cp -R src bin config composer.json package.json package-lock.json "$TMP/package/"
cp tests/fixtures/invoice.pdf "$TMP/app/invoice.pdf"
cat > "$TMP/app/composer.json" <<'JSON'
{
  "name": "assertpdf/install-smoke",
  "repositories": [{"type":"path","url":"../package","options":{"symlink":false,"versions":{"craigsimps/assertpdf":"dev-install-smoke"}}}],
  "require-dev": {"craigsimps/assertpdf":"dev-install-smoke","phpunit/phpunit":"11.5.50"},
  "config": {"platform":{"php":"8.3.0"}}
}
JSON
composer install --working-dir="$TMP/app" --no-interaction --prefer-dist
cat > "$TMP/app/check.php" <<'PHP'
<?php
require __DIR__.'/vendor/autoload.php';
if (class_exists(Illuminate\Foundation\Application::class) || class_exists(Pest\Expectation::class) || class_exists(Spatie\LaravelPdf\PdfBuilder::class) || class_exists(Barryvdh\DomPDF\PDF::class)) {
    throw new RuntimeException('The standalone package installed an optional dependency.');
}
try {
    AssertPdf\PdfAssertions::assertPdfPageCount(__DIR__.'/invoice.pdf', 1);
    throw new LogicException('Missing renderer must be an error.');
} catch (RuntimeException $error) {
    if (!str_contains($error->getMessage(), 'vendor/bin/assertpdf install')) throw $error;
}
PHP
php "$TMP/app/check.php"
"$TMP/app/vendor/bin/assertpdf" install
php -r 'require $argv[1]."/vendor/autoload.php"; AssertPdf\PdfAssertions::assertPdfContainsText($argv[1]."/invoice.pdf", "Invoice 1001"); AssertPdf\PdfAssertions::assertPdfPageCount($argv[1]."/invoice.pdf", 1);' "$TMP/app"
composer require --working-dir="$TMP/app" --dev orchestra/testbench:10.12.0 --no-interaction --prefer-dist
cat > "$TMP/app/InstallTest.php" <<'PHP'
<?php
final class InstallTest extends Orchestra\Testbench\TestCase
{
    public function ignorePackageDiscoveriesFrom() { return []; }

    public function testDiscoveredProviderAndArtisanInstall(): void
    {
        self::assertTrue($this->app->providerIsLoaded(AssertPdf\AssertPdfServiceProvider::class));
        self::assertSame(0, $this->app['config']->get('assertpdf.max_diff_pixels'));
        $this->artisan('assertpdf:install')->assertSuccessful();
        $this->app['router']->get('/invoice', fn () => response()->file(__DIR__.'/invoice.pdf', ['Content-Type' => 'application/pdf']));
        AssertPdf\PdfAssertions::assertPdfContainsText($this->get('/invoice'), 'Invoice 1001');
        AssertPdf\PdfAssertions::assertPdfPageCount($this->get('/invoice'), 1);
    }
}
PHP
# Point Testbench's skeleton at the consumer's installed package manifest.
(cd "$TMP/app" && COMPOSER_VENDOR_DIR="$TMP/app/vendor" vendor/bin/phpunit --bootstrap vendor/autoload.php InstallTest.php)
echo "Fresh standalone and Laravel consumer installation checks passed ($ROOT)."
