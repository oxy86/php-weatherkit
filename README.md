# PhpWeatherKit

[![CI](https://github.com/oxy86/php-weatherkit/actions/workflows/ci.yml/badge.svg)](https://github.com/oxy86/php-weatherkit/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/php-%3E%3D8.1-blue)
![License](https://img.shields.io/badge/license-MIT-green)

A PHP library for [Apple's WeatherKit REST API](https://developer.apple.com/documentation/weatherkitrestapi) (the successor of Dark Sky). It signs the required developer token (JWT, ES256) with your private key, requests the datasets you choose and returns typed objects (`WeatherData`, `DataBlock`, `DataPoint`, `Alert`).

**Status: beta (0.2).** It works against the live API and has a test suite, but the API may still change before 1.0. See the [CHANGELOG](CHANGELOG.md) and the [ROADMAP](ROADMAP.md).

> **Unofficial.** This project is not affiliated with or endorsed by Apple. "WeatherKit" and "Apple Weather" are trademarks of Apple Inc. If you display WeatherKit data you must follow Apple's [attribution requirements](https://developer.apple.com/weatherkit/get-started/#attribution).

## Features
- Developer-token (JWT, ES256) generation from your `.p8` key — no manual token handling
- Fluent request API: `location()`, `language()`, `countryCode()`, `timezone()`, `datasets()`
- All WeatherKit datasets: `currentWeather`, `forecastNextHour`, `forecastHourly`, `forecastDaily`, `weatherAlerts`
- Typed response objects: `WeatherData` → `currently()`, `minutely()`, `hourly()`, `daily()`, `alerts()`; each block exposes `dataPoints()`, `summary()`, `metadata()`, `toArray()`
- Bring your own HTTP client (any PSR-18 client; Guzzle by default) and logger (PSR-3)
- Typed exceptions for authentication, rate-limit, API and validation errors
- Developer token is cached and renewed automatically
- Input validation (coordinates, language, country code, timezone, datasets) before any request is sent
- Batch helper: read many locations from a JSON file and export results per location

## Requirements
- PHP 8.1+ with `ext-json`, `ext-openssl`, cURL (PSR-3 logger optional)
- An Apple Developer Program membership with a WeatherKit **Key ID**, **Team ID**, **Service ID** and the `.p8` private key ([how to create them](https://developer.apple.com/documentation/weatherkitrestapi/request_authentication_for_weatherkit_rest_api))

## Install
```bash
composer require oxy86/php-weatherkit
```
(Until it is on Packagist, clone this repository and run `composer install`.)

## Quick start
Configure the library with an array (or a `Config` object) — see [`examples/config.sample.php`](examples/config.sample.php) for all keys. The private key can be passed as a file path or as a string (e.g. from an environment variable).

```php
use Oxy86\PhpWeatherKit\PhpWeatherKit;

require 'vendor/autoload.php';

$api = new PhpWeatherKit([
    'team_id'          => 'YYYYYYYYYY',
    'key_id'           => 'XXXXXXXXXX',
    'service_id'       => 'com.example.weatherkit',
    'private_key_path' => '/path/to/AuthKey_XXXXXXXXXX.p8',
    'language'         => 'en',
    'timezone'         => 'UTC',
    'country_code'     => 'US',
]);
// Optional: pass any PSR-3 logger as 2nd argument to see debug messages.

$weather = $api->location(51.5074, -0.1278)   // latitude, longitude
    ->language('en')
    ->countryCode('GB')                        // needed for weather alerts
    ->timezone('Europe/London')
    ->datasets(['currentWeather', 'forecastDaily'])
    ->getWeatherData();

$now = $weather->currently();
echo $now->conditionCode(), ' ', $now->temperature(), "°C\n";

foreach ($weather->daily()->dataPoints() as $day) {
    echo substr($day->forecastStart(), 0, 10), ' ', $day->temperatureMin(), '–', $day->temperatureMax(), "\n";
}
```
Values you don't set per request fall back to the defaults in the configuration. (A legacy `settings.php` is still supported: pass the directory that contains it instead of an array.)

## HTTP client and logging
By default the library uses Guzzle. Pass any [PSR-18](https://www.php-fig.org/psr/psr-18/) client and [PSR-17](https://www.php-fig.org/psr/psr-17/) request factory, and a [PSR-3](https://www.php-fig.org/psr/psr-3/) logger, to the constructor:

```php
$api = new PhpWeatherKit($config, $logger, $httpClient, $requestFactory);
```
The client must not throw on HTTP error statuses (Guzzle: `['http_errors' => false]`, which is the default the library uses); the library maps them to exceptions itself.

## Error handling
All exceptions implement `Oxy86\PhpWeatherKit\Exception\ExceptionInterface`.

| Exception | When |
|---|---|
| `ConfigurationException` | bad/missing configuration, key file, locations file or export directory |
| `ValidationException` | invalid coordinates, language, country code, timezone or datasets (thrown before any request) |
| `AuthenticationException` | HTTP 401/403 — wrong credentials or WeatherKit not enabled for your service |
| `RateLimitException` | HTTP 429 |
| `ApiException` | any other HTTP error (`statusCode()`, `responseBody()`), network failure (status 0) or an unreadable response |

```php
try {
    $weather = $api->location($lat, $lng)->getWeatherData();
} catch (\Oxy86\PhpWeatherKit\Exception\RateLimitException $e) {
    // back off and retry later
} catch (\Oxy86\PhpWeatherKit\Exception\ExceptionInterface $e) {
    // anything else from this library
}
```

## Runnable example
```bash
composer install
cp examples/config.sample.php examples/config.php   # add your credentials
cp /path/to/AuthKey_XXXXXXXXXX.p8 examples/         # or point 'private_key_path' to it
php examples/index.php
```
It prints current conditions and a 5-day forecast for the cities in [`examples/locations.json`](examples/locations.json) and, if `export_dir` is set, writes one JSON file per city.

`locations.json` format:
```json
{ "london": { "name": "London", "code": "london", "coords": [51.5074, -0.1278],
              "timezone": "Europe/London", "country_code": "GB", "language": "en" } }
```

## Attribution (Apple requirement)
Apple requires apps that show WeatherKit data to display the Apple Weather trademark and a link to the legal source. `$weather->currently()->metadata()` exposes `attributionURL()` and `providerLogo()` for this.

## Contributing
Issues and pull requests are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md) and the [ROADMAP](ROADMAP.md). Report security problems privately, see [SECURITY.md](SECURITY.md).

## License & credits
MIT — see [LICENSE.md](LICENSE.md). Large parts of this library derive from Dmitry Ivanov's [DarkSkyApi](https://github.com/dmitry-ivanov/dark-sky-api) (MIT), especially `src/Weather`, `src/Http` and `src/Validation`.
