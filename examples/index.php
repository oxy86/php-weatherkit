<?php

/**
 * Example CLI runner for PhpWeatherKit.
 *
 * Setup:
 *   1. composer install   (from the repository root)
 *   2. cp examples/config.sample.php examples/config.php   and fill in your Apple credentials
 *   3. put your .p8 key where WEATHERKIT_PRIVATE_KEY_FILE_PATH points
 *   4. php examples/index.php
 *
 * For every location in locations.json it prints the current conditions and a short daily
 * forecast. If `export_dir` is set in the config it also writes one JSON file per location.
 */

use Oxy86\PhpWeatherKit\PhpWeatherKit;

use function Oxy86\PhpWeatherKit\conditionCodeToIconName;

require_once __DIR__ . '/../vendor/autoload.php';

$api = new PhpWeatherKit(require __DIR__ . '/config.php');

foreach ($api->getGeolocations() ?: [] as $key => $location) {
  if (empty($location['coords'][0]) || empty($location['coords'][1])) {
    fwrite(STDERR, "Skipping '$key': missing coordinates\n");
    continue;
  }
  $code = $location['code'] ?? $key;
  if (!preg_match('/^[-_a-zA-Z0-9]+$/', $code)) {
    fwrite(STDERR, "Skipping '$key': invalid code '$code' (allowed: a-z A-Z 0-9 - _)\n");
    continue;
  }
  $name = $location['name'] ?? $code;

  // Per-location values override the defaults from the configuration.
  $weather = $api->location($location['coords'][0], $location['coords'][1])
    ->language($location['language'] ?? $api->getParameters()->getLanguage())
    ->countryCode($location['country_code'] ?? $api->getParameters()->getCountryCode())
    ->datasets($location['datasets'] ?? $api->getParameters()->getDatasets())
    ->timezone($location['timezone'] ?? $api->getParameters()->getTimeZone())
    ->getWeatherData();

  echo "== $name ==\n";

  $now = $weather->currently();
  $export = ['name' => $name, 'code' => $code];
  if ($now) {
    printf("Now: %s, %.1f°C (feels like %.1f°C), humidity %.0f%%\n",
      $now->conditionCode(), $now->temperature(), $now->temperatureApparent(), $now->humidity() * 100);
    $export['currently'] = [
      'conditionCode' => $now->conditionCode(),
      'icon' => conditionCodeToIconName($now->conditionCode()),
      'temperature' => $now->temperature(),
      'temperatureApparent' => $now->temperatureApparent(),
      'humidity' => $now->humidity(),
    ];
  }

  $daily = $weather->daily();
  if ($daily) {
    foreach (array_slice($daily->dataPoints(), 0, 5) as $day) {
      printf("%s: %s, %.0f° / %.0f°C\n",
        substr($day->forecastStart(), 0, 10), $day->conditionCode(), $day->temperatureMin(), $day->temperatureMax());
      $export['daily'][] = [
        'date' => substr($day->forecastStart(), 0, 10),
        'conditionCode' => $day->conditionCode(),
        'temperatureMin' => $day->temperatureMin(),
        'temperatureMax' => $day->temperatureMax(),
      ];
    }
  }

  if ($api->getExportDir()) {
    $api->saveDataToFile($api->getExportDir() . $code . '.json', json_encode($export, JSON_PRETTY_PRINT), LOCK_EX);
  }
  echo "\n";
}
