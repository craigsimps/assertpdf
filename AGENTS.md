# PDF testing toolkit

Milestone scope: https://github.com/craigsimps/laravel-pdf-testing/issues/2.
The assigned milestone branch may be pushed for CI. Do not merge, open PRs or publish packages.

## Setup

- PHP 8.3+, Composer 2, Node 24.21.0 and npm 11.
- `composer install`
- `php bin/pdf-testing install` — pinned renderer setup inside the package.

## Checks

- `composer test` — full Pest/PHPUnit fixture suite.
- `composer validate --strict`
- `composer lint` — PHP syntax and Node syntax checks.
- `sh tests/installation.sh` — disposable PHPUnit-only and Testbench consumer installation checks (network required).
- `npm run fixtures` — regenerate deterministic test PDFs after intentional fixture changes.
- `docker build --platform linux/amd64 -f Dockerfile.ci -t pdf-testing-ci .`
- `docker run --rm --network none pdf-testing-ci` — pinned Linux checks without network access.
- Repeat the Docker build/run with `--build-arg OPTIONAL_GENERATORS=0` and a separate image tag to check optional-generator absence.

Assertions must stay offline. Keep pdf.js, pixelmatch and canvas versions pinned.
Never silently create or update snapshot baselines. Independent review is arranged by Office.
