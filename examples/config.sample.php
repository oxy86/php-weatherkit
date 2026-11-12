<?php

/**
 * Example configuration. Copy to config.php (git-ignored) and fill in your values.
 * See Oxy86\PhpWeatherKit\Config for all supported keys.
 *
 * @see https://developer.apple.com/documentation/weatherkitrestapi/request_authentication_for_weatherkit_rest_api
 */
return [
  // Credentials from your Apple Developer account
  'team_id'    => 'YYYYYYYYYY',                 // 10-character Team ID
  'key_id'     => 'XXXXXXXXXX',                 // WeatherKit Key ID
  'service_id' => 'com.example.weatherkit',     // your registered Service ID

  // The private key: either a path to the .p8 file...
  'private_key_path' => __DIR__ . '/AuthKey_XXXXXXXXXX.p8',
  // ...or its contents (e.g. from an environment variable / secret store):
  // 'private_key' => getenv('WEATHERKIT_PRIVATE_KEY'),

  // Request defaults (can be overridden per request)
  'language'     => 'en',
  'timezone'     => 'UTC',
  'country_code' => 'US',   // needed for weather alerts
  // 'datasets'  => ['currentWeather', 'forecastDaily'],   // default: all datasets

  // Optional
  'locations_file' => __DIR__ . '/locations.json',
  'export_dir'     => __DIR__ . '/exported_data',
];
