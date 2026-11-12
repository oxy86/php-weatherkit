<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Tests;

use Oxy86\PhpWeatherKit\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public static function valid(): array
    {
        return [
            'team_id' => 'TEAM123456',
            'key_id' => 'KEY1234567',
            'service_id' => 'com.example.weatherkit',
            'private_key' => 'pem',
            'language' => 'en',
            'timezone' => 'UTC',
            'country_code' => 'US',
        ];
    }

    public function testFromArray(): void
    {
        $config = Config::fromArray(self::valid());
        $this->assertSame('TEAM123456', $config->get('team_id'));
        $this->assertNull($config->get('export_dir'));
        $this->assertSame('x', $config->get('export_dir', 'x'));
        $this->assertSame('pem', $config->privateKey());
    }

    #[DataProvider('requiredKeys')]
    public function testMissingRequiredValueThrows(string $key): void
    {
        $values = self::valid();
        unset($values[$key]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($key);
        Config::fromArray($values);
    }

    public static function requiredKeys(): array
    {
        return array_map(fn ($k) => [$k], ['team_id', 'key_id', 'service_id', 'language', 'timezone', 'country_code']);
    }

    public function testNeedsSomePrivateKey(): void
    {
        $values = self::valid();
        unset($values['private_key']);
        $this->expectException(\InvalidArgumentException::class);
        Config::fromArray($values);
    }

    public function testUnknownKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('typo_key');
        Config::fromArray(self::valid() + ['typo_key' => 1]);
    }

    public function testPrivateKeyFromFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'p8');
        file_put_contents($file, 'from-file');
        $values = self::valid();
        unset($values['private_key']);
        $config = Config::fromArray($values + ['private_key_path' => $file]);
        $this->assertSame('from-file', $config->privateKey());
        unlink($file);
    }

    public function testMissingPrivateKeyFileThrows(): void
    {
        $values = self::valid();
        unset($values['private_key']);
        $config = Config::fromArray($values + ['private_key_path' => '/nonexistent/key.p8']);
        $this->expectException(\RuntimeException::class);
        $config->privateKey();
    }

    public function testLegacySettingsFile(): void
    {
        $dir = sys_get_temp_dir() . '/pwk_' . uniqid();
        mkdir($dir);
        file_put_contents($dir . '/settings.php', <<<'PHP'
<?php
$settings['WEATHERKIT_TEAM_ID'] = 'TEAM123456';
$settings['WEATHERKIT_KEY_ID'] = 'KEY1234567';
$settings['WEATHERKIT_SERVICE_ID'] = 'com.example.weatherkit';
$settings['WEATHERKIT_PRIVATE_KEY_FILE_PATH'] = '/some/key.p8';
$settings['default_language'] = 'el';
$settings['default_timezone'] = 'Europe/Athens';
$settings['default_country_code'] = 'GR';
$settings['unrelated'] = 'ignored';
PHP);
        $config = Config::fromSettingsFile($dir);
        $this->assertSame('el', $config->get('language'));
        $this->assertSame('/some/key.p8', $config->get('private_key_path'));
        unlink($dir . '/settings.php');
        rmdir($dir);
    }

    public function testMissingSettingsFileThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        Config::fromSettingsFile('/nonexistent');
    }
}
