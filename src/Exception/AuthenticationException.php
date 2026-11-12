<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Exception;

/**
 * WeatherKit rejected the developer token (HTTP 401/403): wrong key/team/service ID, WeatherKit not enabled for the service, or an expired token.
 */
class AuthenticationException extends ApiException
{
}
