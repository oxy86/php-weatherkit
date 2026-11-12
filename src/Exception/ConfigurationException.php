<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Exception;

/**
 * The configuration, key file, locations file or export directory is missing or invalid.
 */
class ConfigurationException extends \InvalidArgumentException implements ExceptionInterface
{
}
