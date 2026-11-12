<?php

namespace Oxy86\PhpWeatherKit\Weather;

use function Oxy86\PhpWeatherKit\array_get;

class DataBlock
{
    /**
     * The data block.
     *
     * @var array
     */
    protected $block;

    /**
     * Create a new instance of the weather data block.
     *
     * @param  array  $block
     * @return void
     */
    public function __construct(array $block)
    {
        $this->block = $block;
    }



    /**
     * The name of the data block, i.e. CurrentWeather
     *
     * @return string|null
     */
    public function name()
    {
        return array_get($this->block, 'name');
    }



    /**
     * Descriptive information about the weather data block.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/metadata
     *
     * @return \Oxy86\PhpWeatherKit\Weather\MetaData|null
     */
    public function metadata()
    {
        $metadata = array_get($this->block, 'metadata');
        if (is_null($metadata)) {
            return null;
        }

        return new Metadata($metadata);
    }


    /**
     * An array of data points, ordered by time.
     *
     * These points together describe the weather conditions at the requested location over time.
     *
     * @return \Oxy86\PhpWeatherKit\Weather\DataPoint[]
     */
    public function dataPoints()
    {

        if (  array_get($this->block, 'hours') ) {
            // Hourly data exists
            $data = array_get($this->block, 'hours');
        }
        elseif (array_get($this->block, 'days')) {
            $data = array_get($this->block, 'days');
        }
        elseif (array_get($this->block, 'minutes')) {
            $data = array_get($this->block, 'minutes');
        }

        return array_map(function (array $point) {
            return new DataPoint($point);
        }, $data);
    }


    /**
     * A machine-readable text summary of this data block.
     *
     * @see \Oxy86\PhpWeatherKit\Weather\DataPoint::conditionCode()
     *
     * @return string|null
     */
    public function conditionCode()
    {
        return array_get($this->block, 'conditionCode');
    }


    /**
     * A URL that provides more information about the forecast. (only in DailyForecast)
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/dailyforecast/dailyforecastdata
     *
     * @return string|null
     */
    public function learnMoreURL()
    {
        return array_get($this->block, 'learnMoreURL');
    }



    /**
     * An array of the forecast summaries (only in nextHourForecast)
     *
     * @see ttps://developer.apple.com/documentation/weatherkitrestapi/nexthourforecast/nexthourforecastdata
     *
     * @return array|null
     */
    public function summary()
    {
        return array_get($this->block, 'summary');
    }




    /**
     * A URL that provides more information about the alerts. (only in weatherAlerts)
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/weatheralertcollection/weatheralertcollectiondata
     *
     * @return string|null
     */
    public function detailsUrl()
    {
        return array_get($this->block, 'detailsUrl');
    }





    /**
     * Get an array representation of the data block.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->block;
    }
}
