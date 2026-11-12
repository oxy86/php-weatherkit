<?php

namespace Oxy86\PhpWeatherKit;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Oxy86\PhpWeatherKit\Validation\Validator;
use Oxy86\PhpWeatherKit\Http\RequestFactory;
use Oxy86\PhpWeatherKit\Http\GuzzleClient;
use Oxy86\PhpWeatherKit\Weather\WeatherData;

use function Oxy86\PhpWeatherKit\json_decode;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


/**
 * Creates an Php-WeatherKit object.
 */
class PhpWeatherKit
{

    /**
     * The configuration.
     *
     * @var \Oxy86\PhpWeatherKit\Config
     */
    protected $config;

    /**
     * The logger (a NullLogger unless one is injected).
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The directory to export data
     *
     * @var string
     */
    protected $export_dir;


    /**
     * The array of locations to request weather data for
     *
     * @var array
     */
    protected $geolocations = [];


    /**
     * The request parameters.
     *
     * @var \Oxy86\PhpWeatherKit\Parameters
     */
    protected $parameters;

    /**
     * The validator service for the parameters.
     *
     * @var \Oxy86\PhpWeatherKit\Validation\Validator
     */
    protected $validator;


    /**
     * The HTTP client (Guzzle).
     *
     * @var \Oxy86\PhpWeatherKit\Http\GuzzleClient
     */
    protected $http_client;

    /**
     * The request factory.
     *
     * @var \Oxy86\PhpWeatherKit\Http\RequestFactory
     */
    protected $request_factory;


    /**
     * Constructs a new instance of the class.
     *
     * @param \Oxy86\PhpWeatherKit\Config|array|string $config
     *   A Config object, an array of configuration values (see Config), or — for compatibility —
     *   the path of a directory containing a legacy settings.php file.
     * @param \Psr\Log\LoggerInterface|null $logger
     *   Optional PSR-3 logger. Debug messages are sent to it; nothing is printed otherwise.
     */
    public function __construct($config, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();

        if (is_array($config)) {
            $config = Config::fromArray($config);
        } elseif (is_string($config)) {
            $config = Config::fromSettingsFile($config);
        } elseif (!$config instanceof Config) {
            throw new \InvalidArgumentException('The configuration must be a Config object, an array or a directory path.');
        }
        $this->config = $config;

        $this->parameters = new Parameters;
        $this->applyDefaultParameters();
        $this->applyExportDir();
        $this->generateWeatherKitToken();

        $this->validator = new Validator;
        $this->http_client = new GuzzleClient;
        $this->request_factory = new RequestFactory($this->parameters->getJWT());

        $locations_file = $this->config->get('locations_file');
        if (!empty($locations_file)) {
            $this->logger->debug("Reading geolocations from '$locations_file'");
            if (empty($this->readGeolocationsFile($locations_file))) {
                throw new \RuntimeException("The locations file '$locations_file' is empty or invalid.");
            }
        }
    }


    /**
     * Returns the parameters object
     * 
     * return \Oxy86\PhpWeatherKit\Parameters
     * 
     */
    public function getParameters() {
        return $this->parameters;
    }




    /**
     * Applies the default request parameters from the configuration.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#query-parameters
     */
    private function applyDefaultParameters(): void
    {
        $c = $this->config;
        $this->parameters->setLanguage($c->get('language'));
        $this->parameters->setTimeZone($c->get('timezone'));
        $this->parameters->setCountryCode($c->get('country_code'));
        // All supported datasets are requested unless specific ones are configured.
        $this->parameters->setDatasets($c->get('datasets') ?: Parameters::getSupportedDatasets());

        if ($c->get('daily_start')) {
            $this->parameters->setDailyStart($c->get('daily_start'));
        }
        if ($c->get('daily_end')) {
            $this->parameters->setDailyEnd($c->get('daily_end'));
        }
        if ($c->get('hourly_start')) {
            $this->parameters->setHourlyStart($c->get('hourly_start'));
        }
        if ($c->get('hourly_end')) {
            $this->parameters->setHourlyEnd($c->get('hourly_end'));
        }
    }



    /**
     * Generates the JSON Web Token (JWT) that authenticates us with the WeatherKit REST API,
     * signing it with the configured private key, and stores it in the parameters.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/request_authentication_for_weatherkit_rest_api
     */
    private function generateWeatherKitToken(): void
    {
        $team_id = $this->config->get('team_id');
        $service_id = $this->config->get('service_id');
        $key_id = $this->config->get('key_id');
        $now = time();

        $payload = [
            'iss' => $team_id,             // Your 10-character Team ID.
            'iat' => $now,                 // Issued-at (seconds since epoch, UTC).
            'exp' => $now + 60 * 5,        // Expiration time.
            'sub' => $service_id,          // Your registered Service ID.
        ];
        $header = [
            'kid' => $key_id,                    // Your 10-character Key ID.
            'id' => $team_id . '.' . $service_id, // Team ID and Service ID separated by a period.
        ];

        $this->logger->debug('Encoding the WeatherKit developer token (JWT)');
        $this->parameters->setJWT(JWT::encode($payload, $this->config->privateKey(), 'ES256', $key_id, $header));
    }



    /**
     * Sets the export directory from the configuration, if any.
     */
    private function applyExportDir(): void
    {
        $export_dir = $this->config->get('export_dir');
        if (!empty($export_dir)) {
            $this->setExportDir($export_dir);
        }
    }



    /**
     * Sets the directory to export weather data.
     * 
     * @param string $export_dir
     *  The absolute path to the export directory
     * 
     * @return bool
     * 
     */
    public function setExportDir(string $export_dir)
    {

        if (empty($export_dir)) {
            return false;
        }

        if (!str_ends_with($export_dir, '/')) {
            $export_dir .= '/';
        }
        if (!is_readable($export_dir)) {
            throw new \RuntimeException("Error! The directory '$export_dir' is not readable.", 1);
        }
        $this->export_dir = $export_dir;

        return true;
    }



    /**
     * Returns the directory to export weather data, if set, otherwise false
     * 
     * @return string|bool
     * 
     */
    public function getExportDir(){
        return !empty($this->export_dir) ? $this->export_dir : false;
    }


    /**
     * Opens a text file with JSON formatted locations and returns the contents as an associative array
     * 
     * @param string $filePath
     *  The absolute path to the JSON file
     * 
     * @return array
     *  The associative array of the cities/places and their geolocations
     * 
     * @see settings.php
     */
    public function readGeolocationsFile($filePath)
    {
        $this->logger->debug("Reading geolocations text file at: " . $filePath);

        if (!is_readable($filePath)) {
            throw new \RuntimeException("Error! The file '$filePath' is not readable.", 1);
        }

        // Get the contents of the JSON file 
        $strJsonFileContents = file_get_contents($filePath);

        // Decode it to PHP array 
        $this->geolocations = json_decode($strJsonFileContents, true);

        return $this->geolocations;
    }


    /**
     * Returns the geolocations, if they are already loaded, otherwise false
     * 
     * @return array|bool
     */
    public function getGeolocations(){
        return !empty($this->geolocations) && is_array($this->geolocations)? $this->geolocations : false;
    }



    /**
     * Set the latitude and the longitude of the desired location.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#path-parameters
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @return $this
     */
    public function location($latitude, $longitude)
    {
        if (empty($latitude)) {
            throw new \RuntimeException("Error! Empty latitude parameter.", 1);
        }
        if (empty($longitude)) {
            throw new \RuntimeException("Error! Empty longitude parameter.", 1);
        }
        $this->parameters->setLatitude($latitude);
        $this->parameters->setLongitude($longitude);

        return $this;
    }


    /**
     * Helper. Sets the language for localized responses.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#path-parameters
     *
     * @param  string  $language
     * @return $this
     */
    public function language($language)
    {
        if (empty($language)) {
            throw new \RuntimeException("Error! Empty language parameter.", 1);
        }
        $this->logger->debug("Setting language: " . $language);
        $this->parameters->setLanguage($language);


        return $this;
    }


    /**
     * Helper. Sets the ISO Alpha-2 country code for the desired location.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#query-parameters
     *
     * @param  string  $country_code
     * @return $this
     */
    public function countryCode($country_code)
    {
        if (empty($country_code)) {
            throw new \RuntimeException("Error! Empty country_code parameter.", 1);
        }

        $this->logger->debug("Setting country code: " . $country_code);
        $this->parameters->setCountryCode($country_code);

        return $this;
    }


    /**
     * Helper. Sets the weather datasets to get for the location.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#query-parameters
     *
     * @param  string|array  $datasets
     * @return $this
     */
    public function datasets($datasets)
    {
        if (empty($datasets)) {
            $this->parameters->setDatasets(Parameters::getSupportedDatasets());
            return $this;
        }

        $datasets = is_array($datasets) ? $datasets : [$datasets];
        $this->logger->debug("Setting datasets: " . implode(',', $datasets));
        $this->parameters->setDatasets($datasets);

        return $this;
    }



    /**
     * Helper. Sets (required) the name of the timezone to use for rolling up weather forecasts into daily forecasts.
     *
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#query-parameters
     *
     * @param  string  $timezone
     * 
     * @return $this
     */
    public function timezone($timezone)
    {
        if (empty($timezone)) {
            throw new \RuntimeException("Error! Empty timezone parameter.", 1);
        }
        $this->logger->debug("Setting timezone: " . $timezone);
        $this->parameters->setTimeZone($timezone);

        return $this;
    }


    /**
     * Creates the weather data Request from Parameters and returns the Weatherkit API response as a WeatherData object
     *
     * @return \Oxy86\PhpWeatherKit\Weather\WeatherData
     *
     * @throws \Exception on HTTP error
     * @throws \Throwable on HTTP error in PHP >=7
     */
    public function getWeatherData()
    {
        $this->logger->debug("Validating the parameters...");
        $this->validator->validate($this->parameters);

        $this->logger->debug("Creating new weather forecast request...");
        $request = $this->request_factory->createForecastRequest($this->parameters);

        $this->logger->debug("Making the request to Weatherkit API to obtain the weather data for this location...");
        $response = $this->http_client->gzip()->request($request);

        $this->logger->debug("Decoding API response...");
        $data = json_decode($response->getBody(), true);

        $this->logger->debug("Weather data size: " . count(array_keys($data)));
        if (!empty($data) && is_array($data)) {
            $this->logger->debug("Weather data keys: " . implode(', ', array_keys($data)));
        }

        return new WeatherData($data, $response->getHeaders());
    }



    /**
     * Saves data to a file safely, not corrupting existing files in case of failure 
     * 
     * @param string $filename
     *   The absolute path to the file
     * 
     * @param string $data
     *  The data to save
     * 
     * @return bool
     * 
     */
    public function saveDataToFile($filename, $data, $flags = 0)
    {

        // Check that the number of bytes written to the file is the same as the size of passed data
        if (file_put_contents($filename . "~", $data, $flags) === strlen($data)) {
            if ( rename($filename . "~", $filename) ) {
                $this->logger->debug("Data saved to file '$filename'. Filesize: " . filesize($filename) . ' bytes');
                return TRUE;
            }
        }
        @unlink($filename . "~");
        return FALSE;
    }
}
