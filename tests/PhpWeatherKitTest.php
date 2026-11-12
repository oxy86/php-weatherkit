<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Tests;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Oxy86\PhpWeatherKit\Config;
use Oxy86\PhpWeatherKit\PhpWeatherKit;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

final class PhpWeatherKitTest extends TestCase
{
    private static string $privatePem;
    private static string $publicPem;

    public static function setUpBeforeClass(): void
    {
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        openssl_pkey_export($key, $pem);
        self::$privatePem = $pem;
        self::$publicPem = openssl_pkey_get_details($key)['key'];
    }

    private function values(array $extra = []): array
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

    private function jwt(PhpWeatherKit $api): string
    {
        return $api->getParameters()->getJWT();
    }

    public function testBuildsFromArrayAndConfigObject(): void
    {
        $this->assertInstanceOf(PhpWeatherKit::class, new PhpWeatherKit($this->values()));
        $this->assertInstanceOf(PhpWeatherKit::class, new PhpWeatherKit(Config::fromArray($this->values())));
    }

    public function testInvalidConfigTypeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PhpWeatherKit(42);
    }

    public function testJwtClaimsHeaderAndSignature(): void
    {
        $api = new PhpWeatherKit($this->values());
        $jwt = $this->jwt($api);

        $payload = JWT::decode($jwt, new Key(self::$publicPem, 'ES256'));
        $this->assertSame('TEAM123456', $payload->iss);
        $this->assertSame('com.example.weatherkit', $payload->sub);
        $this->assertSame(300, $payload->exp - $payload->iat);

        $header = json_decode(JWT::urlsafeB64Decode(explode('.', $jwt)[0]), true);
        $this->assertSame('ES256', $header['alg']);
        $this->assertSame('KEY1234567', $header['kid']);
        $this->assertSame('TEAM123456.com.example.weatherkit', $header['id']);
    }

    public function testDefaultsAreAppliedToParameters(): void
    {
        $api = new PhpWeatherKit($this->values(['hourly_end' => '2030-01-02T00:00:00Z']));
        $p = $api->getParameters();
        $this->assertSame('en', $p->getLanguage());
        $this->assertSame('UTC', $p->getTimeZone());
        $this->assertSame('US', $p->getCountryCode());
        $this->assertSame('2030-01-02T00:00:00Z', $p->getHourlyEnd());
        $this->assertNull($p->getDailyEnd() ?: null);
        $this->assertNotEmpty($p->getDatasets());
    }

    public function testFluentSettersOverrideDefaults(): void
    {
        $api = new PhpWeatherKit($this->values());
        $api->location(51.5, -0.12)->language('el')->countryCode('GR')->timezone('Europe/Athens');
        $this->assertSame('el', $api->getParameters()->getLanguage());
        $this->assertSame('GR', $api->getParameters()->getCountryCode());
    }

    public function testLocationsFileIsLoaded(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'loc');
        file_put_contents($file, '{"a":{"name":"A","coords":[1.5,2.5]}}');
        $api = new PhpWeatherKit($this->values(['locations_file' => $file]));
        $this->assertSame(['a' => ['name' => 'A', 'coords' => [1.5, 2.5]]], $api->getGeolocations());
        unlink($file);
    }

    public function testExportDirGetsTrailingSlash(): void
    {
        $api = new PhpWeatherKit($this->values(['export_dir' => sys_get_temp_dir()]));
        $this->assertSame(sys_get_temp_dir() . '/', $api->getExportDir());
    }

    public function testNothingIsPrintedAndLoggerReceivesDebugMessages(): void
    {
        $logger = new class extends AbstractLogger {
            public array $records = [];
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message];
            }
        };
        ob_start();
        $api = new PhpWeatherKit($this->values(), $logger);
        $api->location(1.0, 2.0)->language('en');
        $output = ob_get_clean();

        $this->assertSame('', $output);
        $this->assertNotEmpty($logger->records);
        $this->assertSame('debug', $logger->records[0][0]);
    }

    public function testSaveDataToFileIsAtomic(): void
    {
        $api = new PhpWeatherKit($this->values());
        $file = tempnam(sys_get_temp_dir(), 'out');
        $this->assertTrue($api->saveDataToFile($file, '{"ok":true}', LOCK_EX));
        $this->assertSame('{"ok":true}', file_get_contents($file));
        $this->assertFileDoesNotExist($file . '~');
        unlink($file);
    }
}
