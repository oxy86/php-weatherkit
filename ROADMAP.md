# Roadmap / TODO

Goal: take PhpWeatherKit from a working, site-derived library (**0.1**, alpha) to a production-quality, maintained open-source package (**1.0**). Tick items off in the commit that completes them.

## 0.2 — publishable beta
Blocks announcing the package.

### Quality
- [x] PHPUnit set up (`composer test`-style: `vendor/bin/phpunit`); config, constructor, logger, JWT, export tests
- [x] Tests with a recorded WeatherKit fixture (London) + hand-written alerts fixture, and a mock PSR-18 client (59 tests, offline)
- [ ] Replace the hand-written `weather-alerts.json` fixture with a recorded one when a live alert is available
- [ ] Unit tests for validation rules (coordinates, language, country code, timezone, datasets)
- [x] Unit tests for JWT generation (throwaway key generated in the test)
- [x] (workflow added, first run pending) GitHub Actions: tests on PHP 8.1–8.4, `composer audit`, static analysis (PHPStan, start at level 5)
- [ ] Code style (PHP-CS-Fixer or PHPCS, PSR-12) enforced in CI

### API / design
- [x] **Configuration without `settings.php`**: `new PhpWeatherKit([...])` / `Config`; legacy `settings.php` still loadable via a directory path. `Settings` singleton and `$GLOBALS['config']` removed (BREAKING vs 0.1)
- [x] Removed `DebugUtils` echo output; optional PSR-3 logger injected instead
- [x] Key can be passed as a string (`private_key`) or path (`private_key_path`), read in one place (`Config::privateKey()`)
- [x] JWT cached and renewed 30 s before expiry
- [x] Typed exceptions (`ConfigurationException`, `ValidationException`, `AuthenticationException`, `RateLimitException`, `ApiException`)
- [x] PSR-18 client / PSR-17 factory injectable (Guzzle is still a hard dependency as the zero-config default; make it `suggest` in 0.3/1.0)
- [x] `declare(strict_types=1)` everywhere (native parameter/return types still to do)
- [ ] Native parameter/return types on public methods, native parameter/return types, remove legacy docblock-only typing
- [ ] Remove dead Dark Sky leftovers (e.g. stale notes in `DataPoint`) and unused Laravel-style helpers
- [x] Removed the namespaced `json_decode` override
- [ ] Move `array_get`/`conditionCodeToIconName` from `src/functions.php` into classes (keep the function as a deprecated wrapper)
- [ ] Support the WeatherKit `availability` endpoint and the `currentWeather` / `forecastDaily` units parameter; check API coverage against Apple's current reference

### Packaging & docs
- [x] CHANGELOG.md (Keep a Changelog)
- [ ] Tag `v0.2.0` and create the GitHub release
- [ ] Publish on Packagist; add auto-update webhook
- [x] README badges: CI, PHP, license (add Packagist version after the first release)
- [x] README: error handling, HTTP client/logging, attribution
- [ ] Documentation: all datasets and DataPoint fields, rate limits/quotas
- [x] `CONTRIBUTING.md`, `SECURITY.md`, issue and PR templates
- [ ] GitHub repo settings: description, topics, enable private vulnerability reporting, branch protection on `main`
- [ ] Confirm package name (`oxy86/php-weatherkit`) is final before the first Packagist release (renaming later is painful)

## 0.3 — usability
- [ ] Optional response caching hook (PSR-16) respecting the `expireTime` in the response metadata
- [ ] Retry with backoff on 429/5xx
- [ ] Bulk helper: concurrent requests for many locations (Guzzle pool) with a result/error report
- [ ] Optional exporter (the "one JSON file per location" feature) as a separate, documented class
- [ ] Unit/format helpers (Beaufort, wind direction → compass, condition code → icon, localized labels)
- [ ] More examples (alerts, hourly chart data, minimal Symfony/Laravel/Drupal snippets)

## 1.0 — stable
- [ ] API frozen, semver promise documented
- [ ] ≥ 80 % test coverage on `src/`
- [ ] Used in production for at least one release cycle (consumed as a Composer package in at least one real deployment)
- [ ] Documentation site or complete README/wiki
- [ ] Optional: integration test job that talks to the real API using a repository secret (manual trigger only)

## Ongoing
- Keep dependencies current (`composer audit` in CI; `firebase/php-jwt` ≥ 7 required — 6.x has advisory PKSA-y2cr-5h3j-g3ys)
- Track Apple WeatherKit API changes (new datasets, deprecations)
- Respond to issues; triage monthly

## Findings to look into
- Requesting `weatherAlerts` as the *only* dataset returned HTTP 400 in a live test (together with `currentWeather` it worked). Investigate whether the library must always add another dataset or Apple changed behavior.
- A JWT signed with a wrong key was still accepted by the API in a live test (with the real Team/Service ID) — do not rely on the library to detect a wrong key; verify against the Apple portal.

## Done
- [x] 2026-11-12: Beaufort bug fix (null for fractional wind speeds), alerts parsing aligned with the WeatherKit schema, latitude/longitude 0 accepted
- [x] 2026-11-12: Config class, PSR-3 logging, first PHPUnit tests (22 passing), live re-verification with string key
- [x] MIT license with the original DarkSkyApi (Dmitry Ivanov) notice
- [x] PHP ≥ 8.1, `firebase/php-jwt` ^7
- [x] Generic examples (`examples/`), no site-specific data or credentials
- [x] Verified end-to-end against the live WeatherKit API
