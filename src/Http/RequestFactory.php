<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Http;

use Oxy86\PhpWeatherKit\Parameters;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Builds the PSR-7 request for the WeatherKit weather endpoint.
 *
 * @see https://developer.apple.com/documentation/weatherkitrestapi/get_api_v1_weather_language_latitude_longitude
 */
class RequestFactory
{
    private const BASE_URL = 'https://weatherkit.apple.com/api/v1/weather';

    private RequestFactoryInterface $factory;

    public function __construct(RequestFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createForecastRequest(Parameters $parameters, string $jwt): RequestInterface
    {
        $uri = sprintf(
            '%s/%s/%s/%s?%s',
            self::BASE_URL,
            rawurlencode((string) $parameters->getLanguage()),
            number_format(round((float) $parameters->getLatitude(), 3), 3, '.', ''),
            number_format(round((float) $parameters->getLongitude(), 3), 3, '.', ''),
            $this->buildQuery($parameters)
        );

        return $this->factory->createRequest('GET', $uri)
            ->withHeader('Authorization', 'Bearer ' . $jwt)
            ->withHeader('Accept', 'application/json');
    }

    /**
     * Builds the query string. Commas, colons and slashes are kept readable; everything else is RFC 3986 encoded.
     */
    public function buildQuery(Parameters $parameters): string
    {
        $datasets = $parameters->getDatasets();
        $query = ['timezone' => $parameters->getTimeZone()];

        if (!empty($datasets)) {
            $query['dataSets'] = is_array($datasets) ? implode(',', $datasets) : $datasets;
        }
        foreach ([
            'dailyStart' => $parameters->getDailyStart(),
            'dailyEnd' => $parameters->getDailyEnd(),
            'hourlyStart' => $parameters->getHourlyStart(),
            'hourlyEnd' => $parameters->getHourlyEnd(),
            'countryCode' => $parameters->getCountryCode(),
        ] as $name => $value) {
            if (!empty($value)) {
                $query[$name] = $value;
            }
        }

        return str_replace(['%2C', '%3A', '%2F'], [',', ':', '/'], http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    }
}
