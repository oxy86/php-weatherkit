<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Tests;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Oxy86\PhpWeatherKit\Exception\ApiException;
use Oxy86\PhpWeatherKit\Exception\AuthenticationException;
use Oxy86\PhpWeatherKit\Exception\ExceptionInterface;
use Oxy86\PhpWeatherKit\Exception\RateLimitException;
use Oxy86\PhpWeatherKit\Exception\ValidationException;
use Oxy86\PhpWeatherKit\Tests\Support\MockClient;
use Oxy86\PhpWeatherKit\Tests\Support\NetworkError;

final class ApiTest extends TestCase
{
    private function ok(): MockClient
    {
        return new MockClient([MockClient::json($this->fixture('weather-london')), MockClient::json($this->fixture('weather-london'))]);
    }

    public function testRequestShape(): void
    {
        $client = $this->ok();
        $this->api($client)->location(51.50741, -0.12781)->language('el')->timezone('Europe/London')->countryCode('GB')->getWeatherData();

        $request = $client->requests[0];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('weatherkit.apple.com', $request->getUri()->getHost());
        $this->assertSame('/api/v1/weather/el/51.507/-0.128', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('Europe/London', $query['timezone']);
        $this->assertSame('GB', $query['countryCode']);
        $this->assertStringContainsString('currentWeather', $query['dataSets']);

        $auth = $request->getHeaderLine('Authorization');
        $this->assertStringStartsWith('Bearer ', $auth);
        $payload = JWT::decode(substr($auth, 7), new Key(self::$publicPem, 'ES256'));
        $this->assertSame('TEAM123456', $payload->iss);
    }

    public function testDatasetsOverrideAndQueryReadability(): void
    {
        $client = $this->ok();
        $this->api($client)->location(1.0, 2.0)->datasets(['currentWeather', 'forecastDaily'])->getWeatherData();
        $this->assertStringContainsString('dataSets=currentWeather,forecastDaily', $client->requests[0]->getUri()->getQuery());
    }

    public function testZeroCoordinatesAreValid(): void
    {
        $client = $this->ok();
        $this->api($client)->location(0.0, 0.0)->getWeatherData();
        $this->assertSame('/api/v1/weather/en/0.000/0.000', $client->requests[0]->getUri()->getPath());
    }

    public function testEmptyCoordinatesThrow(): void
    {
        $this->expectException(ValidationException::class);
        $this->api($this->ok())->location('', 5.0);
    }

    public function testInvalidParametersThrowBeforeAnyRequest(): void
    {
        $client = $this->ok();
        try {
            $this->api($client)->location(123.0, 5.0)->getWeatherData();
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertInstanceOf(ExceptionInterface::class, $e);
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
        $this->assertCount(0, $client->requests);
    }

    public function testTokenIsReusedAcrossRequests(): void
    {
        $client = $this->ok();
        $api = $this->api($client)->location(1.0, 2.0);
        $api->getWeatherData();
        $api->getWeatherData();
        $this->assertSame($client->requests[0]->getHeaderLine('Authorization'), $client->requests[1]->getHeaderLine('Authorization'));
    }

    public function testExpiredTokenIsRenewed(): void
    {
        $client = $this->ok();
        $api = $this->api($client)->location(1.0, 2.0);
        $api->getWeatherData();

        $property = new \ReflectionProperty($api, 'token_expires_at');
        $property->setValue($api, time() + 5);   // about to expire
        sleep(1);                                  // make iat differ
        $api->getWeatherData();

        $this->assertNotSame($client->requests[0]->getHeaderLine('Authorization'), $client->requests[1]->getHeaderLine('Authorization'));
    }

    public function testAuthenticationError(): void
    {
        $api = $this->api(new MockClient([MockClient::json('{"reason":"NOT_ENABLED"}', 401)]))->location(1.0, 2.0);
        try {
            $api->getWeatherData();
            $this->fail('Expected AuthenticationException');
        } catch (AuthenticationException $e) {
            $this->assertSame(401, $e->statusCode());
            $this->assertSame('{"reason":"NOT_ENABLED"}', $e->responseBody());
            $this->assertInstanceOf(ApiException::class, $e);
        }
    }

    public function testRateLimitError(): void
    {
        $this->expectException(RateLimitException::class);
        $this->api(new MockClient([MockClient::json('{}', 429)]))->location(1.0, 2.0)->getWeatherData();
    }

    public function testOtherHttpErrors(): void
    {
        $api = $this->api(new MockClient([MockClient::json('boom', 503)]))->location(1.0, 2.0);
        try {
            $api->getWeatherData();
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertNotInstanceOf(AuthenticationException::class, $e);
            $this->assertSame(503, $e->statusCode());
        }
    }

    public function testNetworkFailureIsWrapped(): void
    {
        $api = $this->api(new MockClient([new NetworkError('connection refused')]))->location(1.0, 2.0);
        try {
            $api->getWeatherData();
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertSame(0, $e->statusCode());
            $this->assertInstanceOf(NetworkError::class, $e->getPrevious());
        }
    }

    public function testInvalidJsonResponse(): void
    {
        $this->expectException(ApiException::class);
        $this->api(new MockClient([MockClient::json('<html>not json</html>')]))->location(1.0, 2.0)->getWeatherData();
    }
}
