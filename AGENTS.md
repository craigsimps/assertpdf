# PDF testing toolkit

Milestone scope: https://github.com/craigsimps/agent-office/issues/2.
Local-only development; do not add a remote, push, or publish.

## Setup

- PHP 8.3+, Composer 2, Node 24.21.0 and npm 11.
- `composer install`
- `npm ci`

## Checks

- `composer test` — full Pest/PHPUnit fixture suite.
- `composer lint` — PHP syntax and Node syntax checks.
- `npm run fixtures` — regenerate deterministic test PDFs after intentional fixture changes.
- `docker build --platform linux/amd64 -f Dockerfile.ci -t pdf-testing-ci .`
- `docker run --rm --network none pdf-testing-ci` — pinned Linux checks without network access.

Assertions must stay offline. Keep pdf.js, pixelmatch and canvas versions pinned.
Never silently create or update snapshot baselines. Independent review is arranged by Office.
