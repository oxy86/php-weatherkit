<?php

namespace Oxy86\PhpWeatherKit\Weather;

use function Oxy86\PhpWeatherKit\array_get;

/**
 * Creates an object to store weather data from WeatherKit REST API, along with the response headers
 * 
 * @see https://developer.apple.com/documentation/weatherkitrestapi/weather
 */
class WeatherData
{
    /**
     * The data.
     *
     * @var array
     */
    protected $data;

    /**
     * The headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Create a new instance with the response from WeatherKit REST API.
     *
     * @param  array  $data
     * @param  array  $headers
     * @return void
     */
    public function __construct(array $data, array $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    /**
     * The HTTP response headers.
     *
     * @return \Oxy86\PhpWeatherKit\Weather\Headers
     */
    public function headers()
    {
        return new Headers($this->headers);
    }


    /**
     * A data point containing the current weather conditions at the requested location.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/currentweather
     * @see https://developer.apple.com/documentation/weatherkitrestapi/currentweather/currentweatherdata
     *
     * @return \Oxy86\PhpWeatherKit\Weather\DataPoint|null
     */
    public function currently()
    {
        $currently = array_get($this->data, 'currentWeather');
        // echo "currently array keys: " . implode(',', array_keys($currently)) . "\n";
        if (is_null($currently)) {
            return null;
        }

        return new DataPoint($currently);
    }

    /**
     * A data block containing the weather conditions minute-by-minute for the next hour.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/nexthourforecast
     * @see https://developer.apple.com/documentation/weatherkitrestapi/nexthourforecast/nexthourforecastdata
     *
     * @return \Oxy86\PhpWeatherKit\Weather\DataBlock|null
     */
    public function minutely()
    {
        $minutely = array_get($this->data, 'forecastNextHour');
        // echo "minutely array keys: " . implode(',', array_keys($minutely)) . "\n";
        if (is_null($minutely)) {
            return null;
        }

        return new DataBlock($minutely);
    }

    /**
     * A data block containing the weather conditions hour-by-hour for the next two days.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/hourlyforecast
     * @see https://developer.apple.com/documentation/weatherkitrestapi/hourweatherconditions
     *
     * @return \Oxy86\PhpWeatherKit\Weather\DataBlock|null
     */
    public function hourly()
    {
        
        $hourly = array_get($this->data, 'forecastHourly');
        // echo "hourly array keys: " . implode(',', array_keys($hourly)) . "\n";
        // print_r($hourly);
        if (is_null($hourly)) {
            return null;
        }

        return new DataBlock($hourly);
    }

    /**
     * A data block containing the weather conditions day-by-day for the next week.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/dailyforecast
     * @see https://developer.apple.com/documentation/weatherkitrestapi/dayweatherconditions
     *
     * @return \Oxy86\PhpWeatherKit\Weather\DataBlock|null
     */
    public function daily()
    {
        $daily = array_get($this->data, 'forecastDaily');
        // echo "daily array keys: " . implode(',', array_keys($daily)) . "\n";
        // print_r($daily);
        if (is_null($daily)) {
            return null;
        }

        return new DataBlock($daily);
    }

    /**
     * Returns a collection of severe weather alerts for a specified location.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/weatheralertcollection
     *
     * @return \Oxy86\PhpWeatherKit\Weather\Alert[]|null
     */
    public function alerts()
    {
        $alerts = array_get($this->data, 'weatherAlerts');

        if (is_null($alerts)) {
            return null;
        }

        return array_map(function (array $alert) {
            return new Alert($alert);
        }, $alerts);
    }



    /**
     * Get an array representation of the forecast response.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
