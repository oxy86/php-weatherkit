<?php

namespace Oxy86\PhpWeatherKit\Weather;

use function Oxy86\PhpWeatherKit\array_get;


/**
 * Creates a container to hold weather data at a certain date time.
 */
class DataPoint
{
    /**
     * The data point.
     *
     * @var array
     */
    protected $point;

    /**
     * Create a new instance of the weather data point.
     *
     * @param  array  $point
     * @return void
     */
    public function __construct(array $point)
    {
        $this->point = $point;
    }


    /**
     * The name of the data point, i.e. CurrentWeather
     *
     * @return string|null
     */
    public function name()
    {
        return array_get($this->point, 'name');
    }



    /**
     * Descriptive information about the weather data.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/metadata
     *
     * @return \Oxy86\PhpWeatherKit\Weather\MetaData|null
     */
    public function metadata()
    {
        $metadata = array_get($this->point, 'metadata');
        if (is_null($metadata)) {
            return null;
        }

        return new Metadata($metadata);
    }



    /**
     * The starting date and time of the forecast. (Only on daily, hourly, next hour)
     *
     * @return string|null
     */
    public function forecastStart()
    {
        return array_get($this->point, 'forecastStart');
    }


    /**
     * The ending date and time of the day. (Only on daily, next hour)
     *
     * @return string|null
     */
    public function forecastEnd()
    {
        return array_get($this->point, 'forecastEnd');
    }


    /**
     * The date and time of the day. (Only on cunrrently)
     *
     * @return string|null
     */
    public function asOf()
    {
        return array_get($this->point, 'asOf');
    }



    /**
     * The UNIX time at which this data point begins.
     *
     * @return int|null
     */
    public function time()
    {
        return array_get($this->point, 'time');
    }


    /**
     * The forecast between 7 AM and 7 PM for the day.
     *
     * @return \Oxy86\PhpWeatherKit\Weather\DataPoint
     */
    public function daytimeForecast()
    {
        if (array_key_exists('daytimeForecast', $this->point)) {
            $data = array_get($this->point, 'daytimeForecast');
            return new DataPoint($data);
        }
    }



    /**
     * The day part forecast between 7 PM and 7 AM for the overnight.
     *
     * @return \Oxy86\PhpWeatherKit\Weather\DataPoint
     */
    public function overnightForecast()
    {
        if (array_key_exists('overnightForecast', $this->point)) {
            $data = array_get($this->point, 'overnightForecast');
            return new DataPoint($data);
        }
    }


    /**
     * The feels-like temperature when considering wind and humidity, at the start of the hour, in degrees Celsius.
     *
     * @return float|null
     */
    public function temperatureApparent()
    {
        return array_get($this->point, 'temperatureApparent');
    }

    /**
     * The percentage of the sky covered with clouds during the period, from 0 to 1.
     *
     * @return float|null
     */
    public function cloudCover()
    {
        return array_get($this->point, 'cloudCover');
    }

    /**
     * The temperature at which relative humidity is 100%, in Celsius. (current and hourly only)
     *
     * @return float|null
     */
    public function temperatureDewPoint()
    {
        return array_get($this->point, 'temperatureDewPoint');
    }

    /**
     * The relative humidity at the start of the hour, from 0 to 1.
     *
     * @return float|null
     */
    public function humidity()
    {
        return array_get($this->point, 'humidity');
    }

    /**
     * An enumeration value indicating the condition at the time.
     * Machine-readable, probably suitable for selecting an icon for display.
     *
     * If defined, this property will have one of the following values:
     * 
     *   "Clear", "Cloudy", "Dust", "Fog", "Haze", "MostlyClear", "MostlyCloudy",
     *   "PartlyCloudy", "ScatteredThunderstorms", "Smoke", "Breezy", "Windy",
     *   "Drizzle", "HeavyRain", "Rain", "Showers", "Flurries", "HeavySnow",
     *   "MixedRainAndSleet", "MixedRainAndSnow", "MixedRainfall", "MixedSnowAndSleet",
     *   "ScatteredShowers", "ScatteredSnowShowers", "Sleet", "Snow", "SnowShowers",
     *   "Blizzard", "BlowingSnow", "FreezingDrizzle", "FreezingRain", "Frigid",
     *   "Hail", "Hot", "Hurricane", "IsolatedThunderstorms", "SevereThunderstorm",
     *   "Thunderstorm", "Tornado", "TropicalStorm"
     *
     * @return string|null
     */
    public function conditionCode()
    {
        return array_get($this->point, 'conditionCode');
    }





    /**
     *  The phase of the moon on the specified day. Only on "daily".
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/moonphase
     *
     * @return string|null
     */
    public function moonPhase()
    {
        return array_get($this->point, 'moonPhase');
    }


    /**
     * The amount of precipitation forecasted to occur during period, in millimeters.
     *
     * @return float|null
     */
    public function precipitationAmount()
    {
        return array_get($this->point, 'precipitationAmount');
    }

    /**
     * The precipitation intensity, in millimeters per hour.
     *
     * @return float|null
     */
    public function precipitationIntensity()
    {
        return array_get($this->point, 'precipitationIntensity');
    }



    /**
     *  The chance of precipitation forecasted to occur during period (minute, hour, day), from 0 to 1.
     *
     * @return float|null
     */
    public function precipitationChance()
    {
        return array_get($this->point, 'precipitationChance');
    }

    /**
     * The type of precipitation forecasted to occur during the period.
     *
     * If defined, this property will have one of the following values:
     * "clear", "precipitation", "rain", "snow", "sleet", "hail", "mixed"
     * 
     * @see https://developer.apple.com/documentation/weatherkitrestapi/precipitationtype
     *
     * @return string|null
     */
    public function precipitationType()
    {
        return array_get($this->point, 'precipitationType');
    }

    /**
     * The sea-level air pressure, in millibars
     *
     * @return float|null
     */
    public function pressure()
    {
        return array_get($this->point, 'pressure');
    }

    /**
     * A human-readable text summary of this data point.
     * 
     * Note: Dropped while transition DarkSky to WeatherKit API
     * 
     * @see https://developer.apple.com/weatherkit/get-started/
     *
     * @return string|null
     */
    public function summary()
    {
        return "";
    }

    /**
     * The time when the top edge of the sun reaches the horizon in the morning. Only on "daily".
     *
     * @return string|null
     */
    public function sunrise()
    {
        return array_get($this->point, 'sunrise');
    }

    /**
     * The time when the top edge of the sun reaches the horizon in the evening.
     *
     * @return string|null
     */
    public function sunset()
    {
        return array_get($this->point, 'sunset');
    }

    /**
     * The current temperature, in degrees Celsius. On "currently", and hourly
     *
     * @return float|null
     */
    public function temperature()
    {
        return array_get($this->point, 'temperature');
    }

    /**
     * The maximum temperature forecasted to occur during the day, in degrees Celsius. Only on "daily".
     *
     * @return float|null
     */
    public function temperatureMax()
    {
        return array_get($this->point, 'temperatureMax');
    }

    /**
     * The minimum temperature forecasted to occur during the day, in degrees Celsius. Only on "daily".
     *
     * @return float|null
     */
    public function temperatureMin()
    {
        return array_get($this->point, 'temperatureMin');
    }


    /**
     * The UV index (hourly, currently)
     *
     * @return float|null
     */
    public function uvIndex()
    {
        return array_get($this->point, 'uvIndex');
    }


    /**
     * The maximum ultraviolet index value during the day. (daily only)
     *
     * @return float|null
     */
    public function maxUvIndex()
    {
        return array_get($this->point, 'maxUvIndex');
    }




    /**
     * The distance at which terrain is visible at the start of the hour, in meters. Only in hourly.
     *
     * @return float|null
     */
    public function visibility()
    {
        return array_get($this->point, 'visibility');
    }

    /**
     * The direction of the wind, in degrees.
     *
     * With true north at 0° and progressing clockwise.
     *
     * @return float|null
     */
    public function windDirection()
    {
        return array_get($this->point, 'windDirection');
    }

    /**
     * The maximum wind gust speed, in kilometers per hour.
     *
     * @return float|null
     */
    public function windGust()
    {
        return array_get($this->point, 'windGust');
    }


    /**
     * The wind speed, in kilometers per hour.
     *
     * @return float|null
     */
    public function windSpeed()
    {
        return array_get($this->point, 'windSpeed');
    }


    /**
     * The wind speed, in kilometers per hour.
     *
     * @return float|null
     * 
     * @see https://en.wikipedia.org/wiki/Beaufort_scale
     * @see https://www.rmets.org/metmatters/beaufort-wind-scale
     */
    public function windSpeedBeaufort()
    {
        $wind_speed = $this->windSpeed();

        if ($wind_speed < 2) {
            return 0;
        } else if ($wind_speed >= 2 && $wind_speed <= 5) {
            return 1;   // Light Air
        } else if ($wind_speed >= 6 && $wind_speed <= 11) {
            return 2;   // Light Breeze
        } else if ($wind_speed >= 12 && $wind_speed <= 19) {
            return 3;   // Gentle Breeze
        } else if ($wind_speed >= 20 && $wind_speed <= 28) {
            return 4;   // Moderate Breeze
        } else if ($wind_speed >= 29 && $wind_speed <= 38) {
            return 5;   // Fresh Breeze
        } else if ($wind_speed >= 39 && $wind_speed <= 49) {
            return 6;   // Strong Breeze
        } else if ($wind_speed >= 50 && $wind_speed <= 61) {
            return 7;   // High wind, moderate gale,near gale 
        } else if ($wind_speed >= 62 && $wind_speed <= 74) {
            return 8;   //  	Gale or Fresh Gale
        } else if ($wind_speed >= 75 && $wind_speed <= 88) {
            return 9;   //  	Strong/severe gale, Highwaves (6 meters).
        } else if ($wind_speed >= 89 && $wind_speed <= 102) {
            return 10;   //  	Storm, Large waves (6-9 meters)
        } else if ($wind_speed >= 103 && $wind_speed <= 117) {
            return 11;   //  	Violent storm, Large waves (9-14 meters)
        } else if ($wind_speed >= 118) {
            return 12;   //  	Hurricane, Largewaves over 14 meters
        }
    }



    /**
     * Get an array representation of the data point.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->point;
    }
}
