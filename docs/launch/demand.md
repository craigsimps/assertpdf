# Demand measurement — free PDF testing toolkit

Goal: learn whether Laravel developers use real-PDF assertions before considering any paid kit. Counts are directional signals, not revenue or unique-user estimates. No telemetry is built into the package and no tracking has been installed.

## Baseline: 2026-10-30 (pre-launch)

| Signal | Observed baseline | Source / caveat |
| --- | --- | --- |
| Packagist installs | **N/A — package not listed**, not an observed zero | GET `https://packagist.org/packages/craigsimps/laravel-pdf-testing.json` returned HTTP 404, `Package not found`. Local/path installs are not counted. |
| GitHub stars | **0** | Repository API `stargazers_count`; repo is private, so this is not a public demand test. |
| GitHub issues | **2 total: 1 open, 1 closed; 0 external reports** | API issues excluding pull requests: #2 and #3, both Craig's internal milestone issues. Earlier milestone 1 lived in agent-office. Do not count internal planning as demand. |
| Landing visits | **N/A — no landing page or analytics yet** | Design/go-live deferred; do not record a synthetic zero. |
| Public demo | **Not published** | Local repo only. README URL is a TODO placeholder. |

Evidence commands: `gh api repos/craigsimps/laravel-pdf-testing` for stars/visibility and `gh api 'repos/craigsimps/laravel-pdf-testing/issues?state=all&per_page=100'` excluding records with `pull_request` for issues. Paginate if needed. Recheck on launch day because private metadata is not a public baseline.

## What to watch after approval and public launch

- **Packagist:** record cumulative downloads and the delta since launch at each checkpoint using the package API (`downloads.total`, and monthly/daily fields if available). CI/reinstalls inflate these counts; never call them unique people or active apps.
- **GitHub:** stars and change since launch; new external installation/bug/feature issues; whether a report identifies a real application/use case. Record resolved onboarding friction too. Exclude Craig's and the Office agents' activity and PRs from external issue counts. Avoid unsolicited outreach; use only voluntarily public feedback, without copying personal data into reports.
- **Landing visits:** optional. Default to **no analytics** until the page host offers a genuinely free, aggregate, privacy-friendly counter (no cookies, fingerprinting or cross-site IDs) that Craig approves. Do not sign up for or pay for analytics. If no suitable counter exists, leave visits N/A; repository/Packagist signals are sufficient. GitHub repository traffic is not landing-page traffic, and unavailable traffic history cannot be reconstructed later.

Store dated checkpoint summaries on issue #3 or a linked follow-up issue: public URLs, counter values/deltas, external reports by theme, known measurement gaps and the next decision. Do not collect email addresses or user-level browsing data.

## Launch-day, two-week and four-week checks

These are a manual plan, **not scheduled jobs**. Anchor dates to the actual public launch, not this private preparation date. If launch were 2026-10-30, two/four weeks would be 2026-11-13 / 2026-11-27; replace those provisional dates when Craig approves launch.

| Check | Record / action |
| --- | --- |
| Launch day (T0) | Confirm package, repo, demo and landing links work. Reset the baseline with actual public counters, release version and date. Craig sends only the launch posts he chooses. |
| T0 + 14 days | Record all signals and deltas. Can a stranger install without help? Group external issues into installation friction, correctness and desired use cases. Propose one bounded docs/fix iteration if evidence supports it; do not start a paid product. |
| T0 + 28 days | Repeat counters; distinguish one-off launch attention from continued installs or concrete usage reports. Recommend maintain free toolkit, improve discovery/onboarding, or pause investment. Consider researching a paid kit only if several independent developers describe repeated real use and unmet needs; download/star counts alone are insufficient. Craig decides any next investment. |

No public activity is an inconclusive signal if distribution never happened. Record whether the draft posts were actually sent before interpreting low counts. Keep the measurement effort small and free.
