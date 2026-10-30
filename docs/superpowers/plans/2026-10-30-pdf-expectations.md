# PDF expectations implementation plan

**Goal:** Implement the authorised milestone in agent-office#2 and scratchpad 1213.
**Architecture:** A Composer library delegates PDF parsing/rendering to a pinned local Node runner. PHPUnit assertions own failure reporting; Pest expectations share those assertions. Baselines are explicit directories, with an opt-in update environment flag.
**Constraints:** No services, publication, Ghostscript, Poppler or assertion-time network. PHP 8.3+, Node 24.21.0. Cross-OS pixel identity is not promised.

## Tasks

- [x] Add deterministic PDF fixtures and public assertion tests for path/bytes text and page counts; observe failure, implement, rerun.
- [x] Add snapshot tests: explicit baseline creation, unchanged match, moved block diff, timestamp mask, page/dimension changes and malformed inputs; observe failure, implement, rerun.
- [x] Register and exercise all Pest expectations, including failing cases and negation.
- [x] Document Composer path installation into a fresh Laravel application, Node setup, PHPUnit equivalents, mask coordinates, snapshot updates and renderer limitations.
- [x] Add Linux CI with pinned PHP/Node; exercise locally where the available environment permits. Validate offline assertions and a fresh Laravel application.
- [ ] Run all checks, inspect diff, commit locally; record evidence and remaining Linux/independent-review gaps on issue #2 and in Solo handoff, then signal Erika.

## Review focus

Tests must distinguish bytes from local paths (never URLs), report extracted text, reject corrupt PDFs, prevent implicit baseline updates, and detect changes outside the mask. A changed page count or page size must not pass a visual comparison.

Independent review belongs to the coordinator's separate reviewer, not the builder's self-check.

## Verification at builder handoff

The fixture suite passed locally and on the pinned Linux/amd64 image with networking disabled (18 tests, 88 assertions). A fresh Laravel 13.11.0 skeleton / framework 13.39.0 passed a Pest 4.7.8 smoke test (3 tests, 12 assertions); after removing Pest, PHPUnit 12.5.33 passed all three assertion APIs (1 test, 3 assertions). Both application checks ran under a network-denying sandbox. The generated baseline and diff were visually inspected.

Hosted GitHub Actions remains unexecuted because the repository must not be published. Office must arrange independent review and decide whether the local run of the identical Linux job satisfies the milestone's CI evidence requirement or defer the hosted-run criterion until publication is authorised.
