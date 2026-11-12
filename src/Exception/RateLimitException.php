<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Exception;

/**
 * WeatherKit rate limit or quota exceeded (HTTP 429).
 */
class RateLimitException extends ApiException
{
}
