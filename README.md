# PhpWeatherKit

A PHP library for [Apple's WeatherKit REST API](https://developer.apple.com/documentation/weatherkitrestapi) (the successor of Dark Sky). It signs the required developer token (JWT, ES256) with your private key, requests the datasets you choose and returns typed objects (`WeatherData`, `DataBlock`, `DataPoint`, `Alert`).

**Status: alpha (0.x).** It works against the live API, but the API will change before 1.0 and there is no test suite yet. See the [ROADMAP](ROADMAP.md).

> **Unofficial.** This project is not affiliated with or endorsed by Apple. "WeatherKit" and "Apple Weather" are trademarks of Apple Inc. If you display WeatherKit data you must follow Apple's [attribution requirements](https://developer.apple.com/weatherkit/get-started/#attribution).

## Features
- Developer-token (JWT, ES256) generation from your `.p8` key — no manual token handling
- Fluent request API: `location()`, `language()`, `countryCode()`, `timezone()`, `datasets()`
- All WeatherKit datasets: `currentWeather`, `forecastNextHour`, `forecastHourly`, `forecastDaily`, `weatherAlerts`
- Typed response objects: `WeatherData` → `currently()`, `minutely()`, `hourly()`, `daily()`, `alerts()`; each block exposes `dataPoints()`, `summary()`, `metadata()`, `toArray()`
- Input validation (coordinates, language, country code, timezone, datasets) before any request is sent
- Batch helper: read many locations from a JSON file and export results per location

## Requirements
- PHP 8.1+ with `ext-json`, `ext-openssl`, cURL
- An Apple Developer Program membership with a WeatherKit **Key ID**, **Team ID**, **Service ID** and the `.p8` private key ([how to create them](https://developer.apple.com/documentation/weatherkitrestapi/request_authentication_for_weatherkit_rest_api))

## Install
```bash
composer require oxy86/php-weatherkit
```
(Until it is on Packagist, clone this repository and run `composer install`.)

## Quick start
The library reads its configuration from a `settings.php` file located in the directory you pass to the constructor (see [`examples/settings.php.sample`](examples/settings.php.sample)).

```php
use Oxy86\PhpWeatherKit\PhpWeatherKit;

require 'vendor/autoload.php';

$api = new PhpWeatherKit('/path/to/dir-containing-settings.php');

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
Values you don't set fall back to the defaults in `settings.php`.

## Runnable example
```bash
composer install
cp examples/settings.php.sample examples/settings.php   # add your credentials
cp /path/to/AuthKey_XXXXXXXXXX.p8 examples/             # or point settings.php to it
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
Issues and pull requests are welcome. Please read the [ROADMAP](ROADMAP.md) first — the open items (tests, CI, configuration API) are where help is most useful. Never include credentials in issues or PRs.

## Security
Never commit your `.p8` key or `settings.php`. The provided `.gitignore` excludes both.

## License & credits
MIT — see [LICENSE.md](LICENSE.md). Large parts of this library derive from Dmitry Ivanov's [DarkSkyApi](https://github.com/dmitry-ivanov/dark-sky-api) (MIT), especially `src/Weather`, `src/Http` and `src/Validation`.
