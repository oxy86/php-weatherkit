<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Tests;

use Oxy86\PhpWeatherKit\Tests\Support\MockClient;

final class WeatherDataTest extends TestCase
{
    private function weather(string $fixture = 'weather-london', array $headers = [])
    {
        $client = new MockClient([MockClient::json($this->fixture($fixture))->withHeader('X-Test', 'yes')]);
        return $this->api($client)->location(51.5074, -0.1278)->getWeatherData();
    }

    public function testCurrentWeather(): void
    {
        $raw = $this->fixture('weather-london')['currentWeather'];
        $current = $this->weather()->currently();

        $this->assertSame($raw['conditionCode'], $current->conditionCode());
        $this->assertSame($raw['temperature'], $current->temperature());
        $this->assertSame($raw['temperatureApparent'], $current->temperatureApparent());
        $this->assertSame($raw['humidity'], $current->humidity());
        $this->assertSame($raw['uvIndex'], $current->uvIndex());
    }

    public function testDailyForecast(): void
    {
        $raw = $this->fixture('weather-london')['forecastDaily']['days'];
        $days = $this->weather()->daily()->dataPoints();

        $this->assertCount(3, $days);
        $this->assertSame($raw[0]['forecastStart'], $days[0]->forecastStart());
        $this->assertSame($raw[0]['temperatureMax'], $days[0]->temperatureMax());
        $this->assertSame($raw[0]['temperatureMin'], $days[0]->temperatureMin());
        $this->assertSame($raw[0]['sunrise'], $days[0]->sunrise());
        $this->assertNotNull($days[0]->daytimeForecast());
        $this->assertNotNull($days[0]->overnightForecast());
    }

    public function testHourlyAndMinutelyForecast(): void
    {
        $weather = $this->weather();
        $this->assertCount(4, $weather->hourly()->dataPoints());
        $this->assertCount(5, $weather->minutely()->dataPoints());
    }

    public function testMetadataExposesAttributionData(): void
    {
        $metadata = $this->weather()->currently()->metadata();
        $raw = $this->fixture('weather-london')['currentWeather']['metadata'];

        $this->assertSame($raw['attributionURL'], $metadata->attributionURL());
        $this->assertSame($raw['latitude'], $metadata->latitude());
        $this->assertSame($raw['units'], $metadata->units());
    }

    public function testBeaufortConversion(): void
    {
        $current = $this->weather()->currently();
        $this->assertIsInt($current->windSpeedBeaufort());
        $this->assertGreaterThanOrEqual(0, $current->windSpeedBeaufort());
        $this->assertLessThanOrEqual(12, $current->windSpeedBeaufort());
    }

    public function testToArrayReturnsTheRawResponse(): void
    {
        $this->assertSame($this->fixture('weather-london'), $this->weather()->toArray());
    }

    public function testResponseHeadersAreAvailable(): void
    {
        $this->assertNotNull($this->weather()->headers());
    }

    public function testAlertsAreNullWhenTheBlockIsAbsent(): void
    {
        $this->assertNull($this->weather()->alerts());
    }

    public function testAlerts(): void
    {
        $alerts = $this->weather('weather-alerts')->alerts();

        $this->assertCount(2, $alerts);
        $this->assertSame('Example County', $alerts[0]->areaName());
        $this->assertSame('severe', $alerts[0]->severity());
        $this->assertSame('Thunderstorm', $alerts[0]->phenomenon());
        $this->assertSame('2026-01-01T14:00:00Z', $alerts[0]->expireTime());
        $this->assertSame(['shelter'], $alerts[0]->responses());
        $this->assertSame('minor', $alerts[1]->severity());
        $this->assertNull($alerts[1]->expireTime());
    }

    public function testEmptyAlertsBlockGivesEmptyArray(): void
    {
        $client = new MockClient([MockClient::json(['weatherAlerts' => ['alerts' => []]])]);
        $weather = $this->api($client)->location(1.0, 2.0)->getWeatherData();
        $this->assertSame([], $weather->alerts());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('beaufortCases')]
    public function testBeaufortBoundaries(float $kmh, int $force): void
    {
        $client = new MockClient([MockClient::json(['currentWeather' => ['name' => 'CurrentWeather', 'windSpeed' => $kmh]])]);
        $current = $this->api($client)->location(1.0, 2.0)->getWeatherData()->currently();
        $this->assertSame($force, $current->windSpeedBeaufort());
    }

    public static function beaufortCases(): array
    {
        return [
            'calm' => [0.0, 0], 'just under 2' => [1.99, 0], 'light air' => [2.0, 1],
            'gap 5-6 (was null)' => [5.5, 1], 'light breeze' => [6.0, 2], 'gap 11-12 (was null)' => [11.57, 2],
            'gentle breeze' => [12.0, 3], 'gap 19-20' => [19.9, 3], 'gap 28-29' => [28.5, 4], 'fresh' => [29.0, 5],
            'gale' => [62.0, 8], 'storm' => [100.0, 10], 'violent storm' => [117.9, 11], 'hurricane' => [118.0, 12], 'extreme' => [250.0, 12],
        ];
    }
}
