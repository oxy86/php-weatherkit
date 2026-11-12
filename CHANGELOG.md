# Changelog

All notable changes are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versions follow [Semantic Versioning](https://semver.org/) (while 0.x, minor versions may contain breaking changes).

## [0.2.0] - 2026-11-12

### Added
- `Config` class; the constructor accepts an array, a `Config` object or (legacy) a directory containing `settings.php`. The private key can be given as a string (`private_key`) or a path (`private_key_path`).
- Optional PSR-3 logger, PSR-18 HTTP client and PSR-17 request factory (constructor arguments 2–4).
- Typed exceptions under `Oxy86\PhpWeatherKit\Exception` (`ConfigurationException`, `ValidationException`, `ApiException`, `AuthenticationException`, `RateLimitException`, all implementing `ExceptionInterface`).
- The developer token (JWT) is reused and renewed shortly before it expires.
- PHPUnit test suite (offline, with recorded and hand-written fixtures) and a GitHub Actions workflow (PHP 8.1–8.4, `composer audit`).
- `declare(strict_types=1)` in all source files.

### Changed (breaking)
- `new PhpWeatherKit()` without arguments is gone; pass a configuration.
- The `Settings` singleton, `$GLOBALS['config']`/`debug_messages` and `DebugUtils` are removed; the library no longer prints anything.
- `Http\GuzzleClient`, `Http\Request` and `Http\Url` are removed (replaced by PSR-18/PSR-17).
- HTTP failures throw `ApiException` and subclasses instead of Guzzle exceptions. Validation errors still extend `\InvalidArgumentException`.
- `Weather\Alert` and `WeatherData::alerts()` follow the WeatherKit schema (`weatherAlerts.alerts[]`: `areaName()`, `severity()`, `phenomenon()`, `expireTime()`, …); the Dark Sky era accessors (`title()`, `regions()`, `uri()`, `time()`, `expires()`) are removed.
- Requires PHP ≥ 8.1 and `firebase/php-jwt` ^7 (6.x has a security advisory).
- Query strings are RFC 3986 encoded (commas, colons and slashes stay readable).

### Fixed
- `DataPoint::windSpeedBeaufort()` returned `null` for fractional wind speeds that fell between two integer ranges (e.g. 11.57 km/h).
- Latitude/longitude `0` was rejected as "empty".
- `hourly_end` was only applied when `daily_end` was set.

## [0.1.0]
- Initial version, derived from [dmitry-ivanov/dark-sky-api](https://github.com/dmitry-ivanov/dark-sky-api).
