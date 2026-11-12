<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Exception;

/**
 * Marker interface implemented by every exception this library throws on purpose.
 * Catch it to handle all library errors in one place.
 */
interface ExceptionInterface extends \Throwable
{
}
