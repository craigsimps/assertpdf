# PDF testing toolkit

Milestone scope: https://github.com/craigsimps/assertpdf/issues/3, part 1.
Only `milestone-3-release-readiness` may be pushed for CI. Do not merge, open PRs,
push tags, change visibility or publish packages. `v0.1.0-rc.1` is local only.
The landing site and brand pack live in craigsimps/assertpdf-com. This package keeps the README wordmark files in `docs/logo/`.

## Setup

- PHP 8.3/8.4, Composer 2, Node 24.21.0 and npm 11.
- `composer install`
- `php bin/assertpdf install` — pinned renderer setup inside the package.

## Checks

- `composer test` — full Pest/PHPUnit fixture suite.
- `composer validate --strict`
- `composer lint` — PHP syntax and Node syntax checks.
- `sh tests/installation.sh` — disposable PHPUnit-only and Testbench consumer installation checks (network required).
- `npm run fixtures` — regenerate deterministic test PDFs after intentional fixture changes.
- `docker build --platform linux/amd64 -f Dockerfile.ci -t assertpdf-ci .`
- `docker run --rm --network none assertpdf-ci` — pinned Linux checks without network access.
- Repeat the Docker build/run with `--build-arg OPTIONAL_GENERATORS=0` and a separate image tag to check optional-generator absence.
- For PHP 8.4 / Pest 4, copy `PHP_IMAGE` and `PEST_VERSION` build arguments from `.github/workflows/tests.yml`.
- The separate local demo is `../laravel-pdf-testing-demo`; follow its README for setup and `composer test`. Never add a remote without approval.

Assertions must stay offline. Keep pdf.js, pixelmatch and canvas versions pinned.
Never silently create or update snapshot baselines. Independent review is arranged by Office.
