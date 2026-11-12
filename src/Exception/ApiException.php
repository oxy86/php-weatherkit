<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Exception;

/**
 * The request to the WeatherKit API failed: transport error (status code 0), an HTTP error status, or an invalid response.
 */
class ApiException extends \RuntimeException implements ExceptionInterface
{
    private int $statusCode;
    private ?string $responseBody;

    public function __construct(string $message, int $statusCode = 0, ?string $responseBody = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /** The HTTP status code, or 0 when no response was received. */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /** The raw response body, if there was one. */
    public function responseBody(): ?string
    {
        return $this->responseBody;
    }
}
