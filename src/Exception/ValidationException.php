<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Exception;

/**
 * A request parameter (coordinates, language, country code, timezone, datasets...) is invalid.
 * Thrown before any HTTP request is made.
 */
class ValidationException extends \InvalidArgumentException implements ExceptionInterface
{
}
