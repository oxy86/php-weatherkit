<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Oxy86\PhpWeatherKit\Validation\Validator;
use Oxy86\PhpWeatherKit\Exception\ApiException;
use Oxy86\PhpWeatherKit\Exception\AuthenticationException;
use Oxy86\PhpWeatherKit\Exception\ConfigurationException;
use Oxy86\PhpWeatherKit\Exception\RateLimitException;
use Oxy86\PhpWeatherKit\Exception\ValidationException;
use Oxy86\PhpWeatherKit\Http\RequestFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Oxy86\PhpWeatherKit\Weather\WeatherData;


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
     * The PSR-18 HTTP client.
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    protected $http_client;

    /**
     * The current developer token (JWT) and the time it expires.
     *
     * @var string|null
     */
    protected $token;

    /**
     * @var int
     */
    protected $token_expires_at = 0;

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
     * @param \Psr\Http\Client\ClientInterface|null $http_client
     *   Optional PSR-18 HTTP client. Defaults to Guzzle.
     * @param \Psr\Http\Message\RequestFactoryInterface|null $request_factory
     *   Optional PSR-17 request factory. Defaults to the one from guzzlehttp/psr7.
     *
     * @throws \Oxy86\PhpWeatherKit\Exception\ConfigurationException
     */
    public function __construct($config, ?LoggerInterface $logger = null, ?ClientInterface $http_client = null, ?RequestFactoryInterface $request_factory = null)
    {
        $this->logger = $logger ?? new NullLogger();

        if (is_array($config)) {
            $config = Config::fromArray($config);
        } elseif (is_string($config)) {
            $config = Config::fromSettingsFile($config);
        } elseif (!$config instanceof Config) {
            throw new ConfigurationException('The configuration must be a Config object, an array or a directory path.');
        }
        $this->config = $config;

        $this->parameters = new Parameters;
        $this->applyDefaultParameters();
        $this->applyExportDir();
        $this->generateWeatherKitToken();

        $this->validator = new Validator;
        $this->http_client = $http_client ?? $this->defaultHttpClient();
        $this->request_factory = new RequestFactory($request_factory ?? $this->defaultRequestFactory());

        $locations_file = $this->config->get('locations_file');
        if (!empty($locations_file)) {
            $this->logger->debug("Reading geolocations from '$locations_file'");
            if (empty($this->readGeolocationsFile($locations_file))) {
                throw new ConfigurationException("The locations file '$locations_file' is empty or invalid.");
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
        $this->token = JWT::encode($payload, $this->config->privateKey(), 'ES256', $key_id, $header);
        $this->token_expires_at = $payload['exp'];
        $this->parameters->setJWT($this->token);
    }

    /**
     * Returns the developer token, generating a new one when the current one is about to expire.
     */
    private function token(): string
    {
        if ($this->token === null || $this->token_expires_at - time() < 30) {
            $this->generateWeatherKitToken();
        }
        return $this->token;
    }

    private function defaultHttpClient(): ClientInterface
    {
        if (!class_exists(\GuzzleHttp\Client::class)) {
            throw new ConfigurationException('No HTTP client given and Guzzle is not installed. Pass a PSR-18 client or run: composer require guzzlehttp/guzzle');
        }
        return new \GuzzleHttp\Client(['http_errors' => false]);
    }

    private function defaultRequestFactory(): RequestFactoryInterface
    {
        if (!class_exists(\GuzzleHttp\Psr7\HttpFactory::class)) {
            throw new ConfigurationException('No PSR-17 request factory given and guzzlehttp/psr7 is not installed.');
        }
        return new \GuzzleHttp\Psr7\HttpFactory();
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
            throw new ConfigurationException("The directory '$export_dir' is not readable.");
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
            throw new ConfigurationException("The file '$filePath' is not readable.");
        }

        $locations = json_decode((string) file_get_contents($filePath), true);
        if (!is_array($locations)) {
            throw new ConfigurationException("The locations file '$filePath' does not contain valid JSON: " . json_last_error_msg());
        }
        $this->geolocations = $locations;

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
        if ($latitude === null || $latitude === '') {
            throw new ValidationException("Empty latitude parameter.");
        }
        if ($longitude === null || $longitude === '') {
            throw new ValidationException("Empty longitude parameter.");
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
            throw new ValidationException("Empty language parameter.");
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
            throw new ValidationException("Empty country_code parameter.");
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
            throw new ValidationException("Empty timezone parameter.");
        }
        $this->logger->debug("Setting timezone: " . $timezone);
        $this->parameters->setTimeZone($timezone);

        return $this;
    }


    /**
     * Requests the weather data for the current parameters and returns the response as a WeatherData object.
     *
     * @return \Oxy86\PhpWeatherKit\Weather\WeatherData
     *
     * @throws ValidationException      when a request parameter is invalid (no request is made)
     * @throws AuthenticationException  when WeatherKit rejects the developer token (401/403)
     * @throws RateLimitException       when the rate limit/quota is exceeded (429)
     * @throws ApiException             on any other HTTP error, network failure or unreadable response
     */
    public function getWeatherData()
    {
        $this->logger->debug("Validating the parameters...");
        $this->validator->validate($this->parameters);

        $request = $this->request_factory->createForecastRequest($this->parameters, $this->token());
        $this->logger->debug("Requesting weather data: " . $request->getUri());

        try {
            $response = $this->http_client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ApiException('Request to the WeatherKit API failed: ' . $e->getMessage(), 0, null, $e);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status >= 400) {
            $message = "WeatherKit API returned HTTP $status";
            if ($status === 401 || $status === 403) {
                throw new AuthenticationException("$message (authentication failed): $body", $status, $body);
            }
            if ($status === 429) {
                throw new RateLimitException("$message (rate limit exceeded): $body", $status, $body);
            }
            throw new ApiException("$message: $body", $status, $body);
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new ApiException('The WeatherKit API returned an invalid response: ' . json_last_error_msg(), $status, $body);
        }
        $this->logger->debug("Weather data keys: " . implode(', ', array_keys($data)));

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
