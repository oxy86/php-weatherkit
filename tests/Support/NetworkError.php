<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Tests\Support;

use Psr\Http\Client\ClientExceptionInterface;

/** Transport-level failure as a PSR-18 client would raise it. */
final class NetworkError extends \RuntimeException implements ClientExceptionInterface
{
}
