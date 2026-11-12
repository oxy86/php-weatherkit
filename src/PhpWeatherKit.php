<?php

namespace Oxy86\PhpWeatherKit;

use Oxy86\PhpWeatherKit\Utility\DebugUtils;
use Oxy86\PhpWeatherKit\Parameters;
use Oxy86\PhpWeatherKit\Settings;
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
     * The app root, where the src code and the settings.php reside.
     *
     * @var string
     */
    protected $app_root;

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
     * Constructs a new instance of the class
     *
     * @param  string  $key
     * @return void
     */
    public function __construct($app_root = NULL)
    {
        //
        // Set application root 
        //
        if ($app_root === NULL) {
            $app_root = static::guessApplicationRoot();
        }
        $this->app_root = $app_root;
        DebugUtils::e(__METHOD__, "app_root:" . $app_root);

        //
        // Initialize settings
        //
        Settings::initialize($this->app_root);

        //
        // Create parameters container, validator, HTTP client and request factory
        // 
        DebugUtils::e(__METHOD__, "Create parameters object...");
        $this->parameters = new Parameters;

        // Configure default request parameters
        DebugUtils::e(__METHOD__, "Reading default request parameters from settings.php...");
        $this->readDefaultParameterSettings();

        // Check if there is a directory to export defined in settings.php
        DebugUtils::e(__METHOD__, "Checking if there is an 'export_dir' defined in settings.php...");
        $this->readExportDirSetting();

        DebugUtils::e(__METHOD__, "Create validator...");
        $this->validator = new Validator;

        DebugUtils::e(__METHOD__, "Create HTTP client...");
        $this->http_client = new GuzzleClient;

        DebugUtils::e(__METHOD__, "Create RequestFactory...");
        $this->request_factory = new RequestFactory($this->parameters->getJWT());
    }


    /**
     * Determines the (absolute) application root directory based on this file's location. 
     * 
     * The application root directory is where all the source code of the application resides (in `src`), along with the `setting.php`
     *
     * @return string
     *   The absolute path to the application root directory.
     */
    protected static function guessApplicationRoot()
    {
        return dirname(__DIR__);
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
     * Reads the default parameters (dataSets, dailyStart, hourlyStart, timezone, etc) from Settings.
     * 
     * @return bool
     * 
     * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude#query-parameters
     */
    private function readDefaultParameterSettings()
    {

        //
        // REQUIRED. Read the default language to use in the request path from WeatherKit REST API.
        //
        $language = Settings::get('default_language');
        if (empty($language)) {
            throw new \RuntimeException("Error! There is no 'default_language' key defined in settings.php. Check your settings file.", 1);
        }
        $this->parameters->setLanguage($language);


        //
        // REQUIRED. Read the default timezone to use in requests from WeatherKit REST API.
        //
        $timezone = Settings::get('default_timezone');
        if (empty($timezone)) {
            throw new \RuntimeException("Error! There is no 'default_timezone' key defined in settings.php. Check your settings file.", 1);
        }
        $this->parameters->setTimeZone($timezone);


        //
        // Read the datasets we will request from WeatherKit REST API. 
        //
        $datasets = Settings::get('default_datasets');
        if (empty($datasets)) {
            // If not specific datasets have been set, we will request all supported datasets.
            $datasets = Parameters::getSupportedDatasets();
        }
        $this->parameters->setDatasets($datasets);

        //
        // Set the default country code to use in requests from WeatherKit REST API. Necessary for weather alerts.
        //
        $country_code = Settings::get('default_country_code');
        if (empty($country_code)) {
            throw new \RuntimeException("Error! There is no 'default_country_code' key defined in settings.php. Check your settings file.", 1);
        }
        $this->parameters->setCountryCode($country_code);

        
        //
        // Set the default time to start the daily forecast. If this parameter is absent, daily forecasts start on the current day.
        //
        $daily_start = Settings::get('default_daily_start');
        if (!empty($daily_start)) {
            $this->parameters->setDailyStart($daily_start);
        }
        DebugUtils::e(__METHOD__, "daily_start: $daily_start");

        //
        // Set the default time to end the daily forecast. If this parameter is absent, daily forecasts run for 10 days.
        //
        $daily_end = Settings::get('default_daily_end');
        if (!empty($daily_end)) {
            $this->parameters->setDailyEnd($daily_end);
        }
        DebugUtils::e(__METHOD__, "daily_end: $daily_end");


        //
        // The time to start the hourly forecast. If this parameter is absent, hourly forecasts start on the current hour.
        //
        $hourly_start = Settings::get('default_hourly_start');
        if (!empty($hourly_start)) {
            $this->parameters->setHourlyStart($hourly_start);
        }
        DebugUtils::e(__METHOD__, "hourly_start: $hourly_start");

        //
        // The time to end the hourly forecast. If this parameter is absent, hourly forecasts run 24 hours or the length of the daily forecast, whichever is longer.
        //
        $hourly_end = Settings::get('default_hourly_end');
        if (!empty($daily_end)) {
            $this->parameters->setHourlyEnd($hourly_end);
        }
        DebugUtils::e(__METHOD__, "hourly_end: $hourly_end");

        //
        // REQUIRED. Create JWT developer token. It will also be saved in parameters.
        //
        DebugUtils::e(__METHOD__, "Create JWT developer token for authentication with WeatherKit API...");
        $this->generateWeatherKitToken();

        //
        // Set the desired locations to get weather data.
        // Here, we read these locations from a json file, defined in settings.php
        //
        $locations_file = Settings::get('locations_file_path');
        if (!empty($locations_file)) {
            DebugUtils::e(__METHOD__, "Reading geolocations from '$locations_file'...");
            // Read the file into an array
            $geolocations = $this->readGeolocationsFile($locations_file);
            if (empty($geolocations)) {
                throw new \RuntimeException("Error! The locations array is empty. Check your locations file at: $locations_file", 1);
            }
            DebugUtils::e(__METHOD__, "Total locations in file: " . count($geolocations));
        }


        return true;
    }




    /**
     * Generates a JSON Web Token (JWT) from the the user-defined credentials and private key, as 
     * defined in settings.php, to authenticate with the WeatherKit REST API
     * 
     * @return bool
     * 
     * @see https://developer.apple.com/documentation/weatherkitrestapi/request_authentication_for_weatherkit_rest_api
     */
    private function generateWeatherKitToken()
    {
        //
        // Check if the required WeatherKit credentials are defined in settings.php
        //
        if (!Settings::get('WEATHERKIT_PRIVATE_KEY_FILE_PATH')) {
            throw new \RuntimeException("Error! Key 'WEATHERKIT_PRIVATE_KEY_FILE_PATH' not defined or empty in settings.php. Check your settings file.", 1);
        } else if (!Settings::get('WEATHERKIT_KEY_ID')) {
            throw new \RuntimeException("Error! Key 'WEATHERKIT_KEY_ID' not defined or empty in settings.php. Check your settings file.", 1);
        } else if (!Settings::get('WEATHERKIT_TEAM_ID')) {
            throw new \RuntimeException("Error! Key 'WEATHERKIT_TEAM_ID' not defined or empty in settings.php. Check your settings file.", 1);
        } else if (!Settings::get('WEATHERKIT_SERVICE_ID')) {
            throw new \RuntimeException("Error! Key 'WEATHERKIT_SERVICE_ID' not defined or empty in settings.php. Check your settings file.", 1);
        }

        //
        // Get the full path of the file containing the WeatherKit private key
        //
        $private_key_file = Settings::get('WEATHERKIT_PRIVATE_KEY_FILE_PATH');
        // Verify the file is reabable
        if (!is_readable($private_key_file)) {
            throw new \RuntimeException("Error! The private key file '$private_key_file' does not exist or it is not readable. Check the file...", 1);
        }

        // 
        // Read private key
        //
        $weatherkit_private_key = file_get_contents($private_key_file);
        if (empty($weatherkit_private_key)) {
            throw new \RuntimeException("Error! The private key file '$private_key_file' is empty. Check the file...", 1);
        }


        // Assemble our JWT payload data
        $payload = [
            //  The issuer claim key. This value is your 10-character Team ID from your developer account.
            'iss' => Settings::get('WEATHERKIT_TEAM_ID'),
            // The issued-at claim key. 
            // This value indicates the time at which the token was generated. 
            // The value is the number of seconds since epoch in Universal Coordinated Time.
            'iat' => time(),
            // The expiration time claim key. 
            // This value indicates the time after which the token is not accepted by the server. 
            // The value is the number of seconds since epoch in Universal Coordinated Time.
            "exp" => (time() + (60 * 5)),
            //    The subject public claim key. This value is your registered Service ID.
            'sub' => Settings::get('WEATHERKIT_SERVICE_ID')

        ];
        // JWT payload header
        $payload_header = [
            //     A 10-character key identifier you obtain from your developer account.
            "kid" => Settings::get('WEATHERKIT_KEY_ID'),
            //     An identifier that consists of your 10-character Team ID and Service ID, separated by a period.
            "id" => Settings::get('WEATHERKIT_TEAM_ID') . '.' . Settings::get('WEATHERKIT_SERVICE_ID'),
        ];
        // Get key id
        $weatherkit_key_id = Settings::get('WEATHERKIT_KEY_ID');

        // Encode payload + header to JWT string (to debug encoding @see https://jwt.io/)
        DebugUtils::e(__METHOD__, "Encoding payload+header into a JWT string...");
        $jwt = JWT::encode($payload, $weatherkit_private_key, 'ES256', $weatherkit_key_id, $payload_header);
        $this->parameters->setJWT($jwt);
    }



    /**
     * Checks if a directory to export weather data is defined in Settings and verifies it can be read.
     * 
     * @return bool
     * 
     */
    private function readExportDirSetting()
    {
        //
        // Read the default export_dir defined in settings.php, if any
        //
        $export_dir = Settings::get('export_dir');
        if (empty($export_dir)) {
            return false;
        }

        if (!str_ends_with($export_dir, '/')) {
            $export_dir .= '/';
        }
        if (!is_readable($export_dir)) {
            throw new \RuntimeException("Error! The 'export_dir' setting ($export_dir) is not readable. Check your settings file.", 1);
        }
        $this->export_dir = $export_dir;

        return true;
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
        DebugUtils::e(__METHOD__, "Reading geolocations text file at: " . $filePath);

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
        DebugUtils::e(__METHOD__, "Setting language: " . $language);
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

        DebugUtils::e(__METHOD__, "Setting country code: " . $country_code);
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
        DebugUtils::e(__METHOD__, "Setting datasets: " . implode(',', $datasets));
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
        DebugUtils::e(__METHOD__, "Setting timezone: " . $timezone);
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
        DebugUtils::e(__METHOD__, "Validating the parameters...");
        $this->validator->validate($this->parameters);

        DebugUtils::e(__METHOD__, "Creating new weather forecast request...");
        $request = $this->request_factory->createForecastRequest($this->parameters);

        DebugUtils::e(__METHOD__, "Making the request to Weatherkit API to obtain the weather data for this location...");
        $response = $this->http_client->gzip()->request($request);

        DebugUtils::e(__METHOD__, "Decoding API response...");
        $data = json_decode($response->getBody(), true);

        DebugUtils::e(__METHOD__, "Weather data size: " . count(array_keys($data)));
        if (!empty($data) && is_array($data)) {
            DebugUtils::e(__METHOD__, "Weather data keys: " . implode(', ', array_keys($data)));
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
                DebugUtils::e(__METHOD__, "Data saved to file '$filename'. Filesize: " . filesize($filename) . ' bytes');
                return TRUE;
            }
        }
        @unlink($filename . "~");
        return FALSE;
    }
}
