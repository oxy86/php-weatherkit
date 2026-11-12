<?php

namespace Oxy86\PhpWeatherKit\Http;

use Oxy86\PhpWeatherKit\Parameters;
use Oxy86\PhpWeatherKit\Utility\DebugUtils;



/**
 * Creates a Request factory, which generates new Requests
 */
class RequestFactory
{

    /**
     * The JSON Web Token (JWT) to authenticate the app in WeatherKit REST API  
     * Passed from parent. 
     * 
     * @var string
     */
    protected $jwt;

    /**
     * Create a new instance of the request factory.
     *
     * @param  string|null  $private_key_file
     *  The path to the WeatherKit private key file
     * 
     * @return void
     */
    public function __construct(string $jwt = null)
    {
        DebugUtils::e(__METHOD__, "Reading JWT token...");
        $this->jwt = $jwt;
    }

    /**
     * Creates forecast Request(s) using the given parameters to build the request url and query
     *
     * @param  \Oxy86\PhpWeatherKit\Parameters  $parameters
     * @return \Oxy86\PhpWeatherKit\Http\Request|\Oxy86\PhpWeatherKit\Http\Request[]
     */
    public function createForecastRequest(Parameters $parameters)
    {

        DebugUtils::e(__METHOD__, "Generating forecast request url from parameters...");
        $url = $this->generateForecastUrl($parameters);

        DebugUtils::e(__METHOD__, "Building forecast request url query from parameters...");
        $query = $this->buildQuery($parameters);

        if ($url instanceof Url) {
            DebugUtils::e(__METHOD__, "Will create just one request...");
            return $this->createRequest($url, $query);
        }

        // For multiple 
        return array_map(function (Url $url) use ($query) {
            return $this->createRequest($url, $query);
        }, $url);
    }


    /**
     * Compose the forecast URL by the given parameters.
     *
     * @param  \Oxy86\PhpWeatherKit\Parameters  $parameters
     * @return \Oxy86\PhpWeatherKit\Http\Url
     */
    protected function generateForecastUrl(Parameters $parameters)
    {
        $language = $parameters->getLanguage();

        $latitude = round($parameters->getLatitude(), 3);
        $longitude = round($parameters->getLongitude(), 3);

        $latitude = number_format($latitude, 3);
        $longitude = number_format($longitude, 3);

        return new Url("https://weatherkit.apple.com/api/v1/weather/{$language}/{$latitude}/{$longitude}");
    }


        /**
     * Build the request query string by the given parameters.
     *
     * @param  \Oxy86\PhpWeatherKit\Parameters  $parameters
     * @return string
     */
    public function buildQuery(Parameters $parameters)
    {
        if ( is_array($parameters->getDatasets()) ) {
            $datasets = implode(',', $parameters->getDatasets());
        }
        else {
            $datasets = $parameters->getDatasets();
        }
        $data_for_query = [];

        // Required
        $data_for_query['timezone'] = $parameters->getTimeZone();

        if (!empty($datasets)) {
            $data_for_query['dataSets'] = $datasets;
        }
        if (!empty($parameters->getDailyStart())) {
            $data_for_query['dailyStart'] = $parameters->getDailyStart();
        }
        if (!empty($parameters->getDailyEnd())) {
            $data_for_query['dailyEnd'] = $parameters->getDailyEnd();
        }
        if (!empty($parameters->getHourlyStart())) {
            $data_for_query['hourlyStart'] = $parameters->getHourlyStart();
        }
        if (!empty($parameters->getHourlyEnd())) {
            $data_for_query['hourlyEnd'] = $parameters->getHourlyEnd();
        }
        
        if (!empty($parameters->getCountryCode())) {
            $data_for_query['countryCode'] = $parameters->getCountryCode();
        }


        $query = http_build_query($data_for_query);

        DebugUtils::e(__METHOD__, "query: " . $query );

        return urldecode($query);
    }



    /**
     * Creates a new Request using the given URL object and the query string.
     *
     * @param  \Oxy86\PhpWeatherKit\Http\Url  $url
     * @param  string  $query
     * @return \Oxy86\PhpWeatherKit\Http\Request
     */
    protected function createRequest(Url $url, $query)
    {
        DebugUtils::e(__METHOD__, "Creating new Request to url: " . $url->value());
        // Create a request id using ms from unix epoch time
        $id = microtime(true);
        // Create the new Request passing the id, the query, and the JWT credentials
        return new Request($url->value(), $query, $id, $this->jwt);
    }
}
