<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Tests;

use Oxy86\PhpWeatherKit\PhpWeatherKit;
use Oxy86\PhpWeatherKit\Tests\Support\MockClient;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static string $privatePem = '';
    protected static string $publicPem = '';

    public static function setUpBeforeClass(): void
    {
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        openssl_pkey_export($key, $pem);
        self::$privatePem = $pem;
        self::$publicPem = openssl_pkey_get_details($key)['key'];
    }

    protected function values(array $extra = []): array
    {
        return $extra + [
            'team_id' => 'TEAM123456',
            'key_id' => 'KEY1234567',
            'service_id' => 'com.example.weatherkit',
            'private_key' => self::$privatePem,
            'language' => 'en',
            'timezone' => 'UTC',
            'country_code' => 'US',
        ];
    }

    protected function fixture(string $name): array
    {
        return json_decode((string) file_get_contents(__DIR__ . '/fixtures/' . $name . '.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function api(MockClient $client, array $config = []): PhpWeatherKit
    {
        return new PhpWeatherKit($this->values($config), null, $client);
    }
}
