<?php

namespace Oxy86\PhpWeatherKit;

use InvalidArgumentException;

/**
 * Get an item from the array by its key.
 *
 * @param  array  $array
 * @param  string  $key
 * @param  mixed  $default
 * @return mixed
 */
function array_get(array $array, $key, $default = null)
{
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Wrapper for the `json_decode()` that throws when an error occurs.
 *
 * @link http://www.php.net/manual/en/function.json-decode.php
 *
 * @param  string  $json
 * @param  bool  $assoc
 * @param  int  $depth
 * @param  int  $options
 * @return mixed
 *
 * @throws \InvalidArgumentException
 */
function json_decode($json, $assoc = false, $depth = 512, $options = 0)
{
    $data = \json_decode($json, $assoc, $depth, $options);

    if (json_last_error() != JSON_ERROR_NONE) {
        $message = json_last_error_msg();
        throw new InvalidArgumentException("[json_decode] error: {$message}.");
    }

    return $data;
}




/**
 * Translates condition codes to icon names
 * 
 * @see WMO icons: https://worldweather.wmo.int/en/wxicons.html
 * @see Apple icons: https://support.apple.com/guide/iphone/learn-the-weather-icons-iph4305794fb/ios
 */
function conditionCodeToIconName(?string $condition_text = null)
{
    $icon_name = 'unknown';
    switch ($condition_text) {
        case 'Clear':
            $icon_name = 'bright-sunny-fair-fine-clear-daytime';
            break;
        case 'Cloudy':
            $icon_name = 'cloudy-mostly-cloudy-daytime';
            break;
        case 'Dust':
            $icon_name = 'dust-sand-sandstorm';
            break;
        case 'Fog':
            $icon_name = 'fog';
            break;
        case 'Haze':
            $icon_name = 'fog';
            break;
        case 'MostlyClear':
            $icon_name = 'sunny-periods-partly-cloudy-partly-bright-mild-daytime';
            break;
        case 'MostlyCloudy':
            $icon_name = 'sunny-intervals-no-rain-clearing-daytime';
            break;
        case 'PartlyCloudy':
            $icon_name = 'sunny-periods-partly-cloudy-partly-bright-mild-daytime';
            break;
        case 'ScatteredThunderstorms':
            $icon_name = 'storm-thunderstorm';
            break;
        case 'Smoke':
            $icon_name = 'smoke';
            break;
        case 'Breezy':
            $icon_name = 'windy-squall-stormy-gale';
            break;
        case 'Windy':
            $icon_name = 'windy-squall-stormy-gale';
            break;
        case 'Drizzle':
            $icon_name = 'drizzle-light-rain';
            break;
        case 'HeavyRain':
            $icon_name = 'showers-heavy-showers-rainshower';
            break;
        case 'Rain':
            $icon_name = 'rain';
            break;
        case 'Showers':
            $icon_name = 'showers-heavy-showers-rainshower';
            break;
        case 'Flurries':
            $icon_name = 'snow-showers-flurries';
            break;
        case 'HeavySnow':
            $icon_name = 'snow-heavysnow-snowfall';
            break;
        case 'MixedRainAndSleet':
            $icon_name = 'sleet';
            break;
        case 'MixedRainAndSnow':
            $icon_name = 'freezing-rain';
            break;
        case 'MixedRainfall':
            $icon_name = 'rain';
            break;
        case 'MixedSnowAndSleet':
            $icon_name = 'sleet';
            break;
        case 'ScatteredShowers':
            $icon_name = 'rain';
            break;
        case 'ScatteredSnowShowers':
            $icon_name = 'snow-showers-flurries';
            break;
        case 'Sleet':
            $icon_name = 'sleet';
            break;
        case 'Snow':
            $icon_name = 'snow';
            break;
        case 'SnowShowers':
            $icon_name = 'snow-showers-flurries';
            break;
        case 'Blizzard':
            $icon_name = 'blowing-snow-blizzard';
            break;
        case 'BlowingSnow':
            $icon_name = 'blowing-snow-blizzard';
            break;
        case 'FreezingDrizzle':
            $icon_name = 'snow';
            break;
        case 'FreezingRain':
            $icon_name = 'freezing-rain';
            break;
        case 'WintryMix':
            $icon_name = 'freezing-rain';
            break;     
        case 'Frigid':
            $icon_name = 'freezing';
            break;
        case 'Hail':
            $icon_name = 'hail';
            break;
        case 'Hot':
            $icon_name = 'hot';
            break;
        case 'Hurricane':
            $icon_name = 'storm-thunderstorm';
            break;
        case 'IsolatedThunderstorms':
            $icon_name = 'storm-thunderstorm';
            break;
        case 'SevereThunderstorm':
            $icon_name = 'storm-thunderstorm';
            break;
        case 'Thunderstorm':
            $icon_name = 'storm-thunderstorm';
            break;
        case 'Tornado':
            $icon_name = 'storm-thunderstorm';
            break;
        case 'TropicalStorm':
            $icon_name = 'storm-thunderstorm';
            break;

        default:
            # code...
            break;
    }
    return $icon_name;
}
