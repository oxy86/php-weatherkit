<?php

namespace Oxy86\PhpWeatherKit;


/**
 * Creates a container for our Request parameters
 */
class Parameters
{

    /**
     * The JSON Web Token (JWT) to authenticate the app in WeatherKit REST API  
     * This is created by the JWT library with the user-provided api key kai credentials 
     * 
     * @var string|null
     */
    protected $jwt;


    /**
     * The language tag to use for localizing responses.
     *
     * @var string|null
     */
    protected $language = 'en';


    /**
     * The latitude.
     *
     * @var float
     */
    protected $latitude;

    /**
     * The longitude.
     *
     * @var float
     */
    protected $longitude;


    /**
     * The ISO Alpha-2 country code for the requested location. This parameter is necessary for weather alerts.
     *
     * @var string|null
     */
    protected $country_code = 'US';


    /**
     * The time to obtain current conditions. Defaults to now.
     *
     * @var string|null
     */
    protected $current_as_of;



    /**
     * The time to start the daily forecast. If this parameter is absent, daily forecasts start on the current day.
     * 
     * Must be HTML date-time 
     *
     * @var string|null
     */
    protected $daily_start;

    /**
     * The time to end the daily forecast. If this parameter is absent, daily forecasts run for 10 days.
     * 
     * Must be HTML date-time 
     *
     * @var string|null
     */
    protected $daily_end;




    /**
     * The weather datasets to request
     *
     * @var array|string|null
     */
    protected $datasets;


    
    /**
     * The default weather datasets
     *
     * @var array
     */
    private const SUPPORTED_DATASETS = ['currentWeather', 'forecastDaily', 'forecastHourly', 'forecastNextHour', 'weatherAlerts'];


    /**
     * The time to start the hourly forecast. If this parameter is absent, hourly forecasts run 24 hours or the length of the daily forecast, whichever is longer.
     * 
     * Must be HTML date-time 
     *
     * @var string|null
     */
    protected $hourly_start;


    /**
     * The time to end the hourly forecast. If this parameter is absent, hourly forecasts run 24 hours or the length of the daily forecast, whichever is longer.
     * 
     * Must be HTML date-time 
     *
     * @var string|null
     */
    protected $hourly_end;



    /**
     *  (Required) The name of the timezone to use for rolling up weather forecasts into daily forecasts.
     *
     * @var string|null
     */
    protected $timezone;

    

    /**
     * Returns the signed JSON Web Token (JWT).
     * This will be presented as a bearer token to the WeatherKit API
     *
     * @return string|null
     */
    public function getJWT()
    {
        return $this->jwt;
    }

    /**
     * Sets the signed JWT.
     *
     * @param  string|null  $jwt
     * @return void
     */
    public function setJWT($jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Get the latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set the latitude.
     *
     * @param  float  $latitude
     * @return void
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Returns the desired longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude.
     *
     * @param  float  $longitude
     * @return void
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }



    /**
     * Returns the desired language.
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the language.
     *
     * @param  string|null  $language
     * @return void
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }



    /**
     * Returns the desired country_code.
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Sets the the ISO Alpha-2 country code for the location. This parameter is necessary for weather alerts.
     *
     * @param  string|null  $country_code
     * @return void
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
    }




    /**
     * Returns the desired  time to start the daily forecast. 
     *
     * @return string|null
     */
    public function getDailyStart()
    {
        return $this->daily_start;
    }

    /**
     * Sets the desired time to start the daily forecast. 
     * If this parameter is absent, daily forecasts start on the current day.
     *
     * @param  string|null  $daily_start
     *  Must be ISO 8601 date format with Z, i.e. `date('Y-m-d\TH:i:sp');`
     * 
     * @return void
     */
    public function setDailyStart($daily_start)
    {
        $this->daily_start = $daily_start;
    }




    /**
     * Returns the desired time to end the daily forecast. 
     *
     * @return string|null
     *   The date sting will be in ISO 8601 date format with Z
     */
    public function getDailyEnd()
    {
        return $this->daily_end;
    }

    /**
     * Sets the desired time to end the daily forecast. 
     *
     * @param  string|null  $daily_end
     *  Must be ISO 8601 date format with Z, i.e. `date('Y-m-d\TH:i:sp');`
     * 
     * @return void
     */
    public function setDailyEnd($daily_end)
    {
        $this->daily_end = $daily_end;
    }




    /**
     * Gets the names of the desired weather datasets
     *
     * @return array|string|null
     */
    public function getDatasets()
    {
        return $this->datasets;
    }


    /**
     * Gets the names of all weather datasets supported by the WeatherKit REST API.
     *
     * @return array
     * 
     * @see https://developer.apple.com/documentation/weatherkitrestapi/dataset#possibleValues
     */
    public static function getSupportedDatasets()
    {
        return self::SUPPORTED_DATASETS;
    }

    /**
     * Stores the names of the weather datasets to get.
     *
     * @param  array|string|null  $datasets
     * @return void
     * 
     * @see https://developer.apple.com/documentation/weatherkitrestapi/dataset#possibleValues
     */
    public function setDatasets($datasets)
    {
        $this->datasets = $datasets;
    }



    /**
     * Returns the desired time to start the hourly forecast. 
     *
     * @return string|null
     */
    public function getHourlyStart()
    {
        return $this->hourly_start;
    }

    /**
     * Sets the desired time to start the hourly forecast. If this parameter is absent,
     *  hourly forecasts start on the current hour.
     *
     * @param  string|null  $hourly_start
     *  Must be ISO 8601 date format with Z, i.e. `date('Y-m-d\TH:i:sp');`
     * 
     * @return void
     */
    public function setHourlyStart($hourly_start)
    {
        $this->hourly_start = $hourly_start;
    }



    /**
     * Returns the desired time to end the hourly forecast. 
     *
     * @return string|null
     */
    public function getHourlyEnd()
    {
        return $this->hourly_end;
    }

    /**
     * Sets the desired time to end the hourly forecast. If this parameter is absent, 
     * hourly forecasts run 24 hours or the length of the daily forecast, whichever is longer.
     *
     * @param  string|null  $hourly_end
     *  Must be ISO 8601 date format with Z, i.e. `date('Y-m-d\TH:i:sp');`
     * 
     * @return void
     */
    public function setHourlyEnd($hourly_end)
    {
        $this->hourly_end = $hourly_end;
    }



    /**
     * Gets the name of the timezone to use
     *
     * @return string|null
     */
    public function getTimeZone()
    {
        return $this->timezone;
    }

    /**
     * Stores the name of the timezone to use
     *
     * @param  string|null  $timezone
     * @return void
     * 
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#query-parameters
     */
    public function setTimeZone($timezone)
    {
        $this->timezone = $timezone;
    }


}
