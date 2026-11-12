<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/** A tiny PSR-18 client that replays queued responses (or throws queued exceptions) and records requests. */
final class MockClient implements ClientInterface
{
    /** @var RequestInterface[] */
    public array $requests = [];

    /** @var array<ResponseInterface|\Throwable> */
    private array $queue;

    /** @param array<ResponseInterface|\Throwable> $queue */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public static function json(array|string $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], is_string($body) ? $body : json_encode($body));
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $next = array_shift($this->queue) ?? throw new \LogicException('MockClient queue is empty');
        if ($next instanceof \Throwable) {
            throw $next;
        }
        return $next;
    }
}
