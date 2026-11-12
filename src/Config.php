<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit;

/**
 * Immutable library configuration.
 *
 * Build it from a plain array (preferred) or, for compatibility, from a legacy `settings.php` file.
 *
 * Array keys:
 *
 * Credentials (required)
 *   team_id, key_id, service_id   Your Apple Developer Team ID, WeatherKit Key ID and Service ID
 *   private_key                   The contents of the .p8 key (PEM string), e.g. from an environment variable
 *   private_key_path              ...or the path of the .p8 file (used when `private_key` is not given)
 *
 * Request defaults (required: language, timezone, country_code; the others are optional)
 *   language, timezone, country_code
 *   datasets                      Array of datasets; all supported datasets when omitted
 *   daily_start, daily_end, hourly_start, hourly_end    ISO 8601 date-time strings
 *
 * Optional
 *   locations_file                Path of a JSON file with locations (see README)
 *   export_dir                    Directory for exported files
 */
final class Config
{
    private const REQUIRED = ['team_id', 'key_id', 'service_id', 'language', 'timezone', 'country_code'];

    private const KNOWN = [
        'team_id', 'key_id', 'service_id', 'private_key', 'private_key_path',
        'language', 'timezone', 'country_code', 'datasets',
        'daily_start', 'daily_end', 'hourly_start', 'hourly_end',
        'locations_file', 'export_dir',
    ];

    /** Legacy settings.php key => Config key. */
    private const LEGACY_MAP = [
        'WEATHERKIT_TEAM_ID' => 'team_id',
        'WEATHERKIT_KEY_ID' => 'key_id',
        'WEATHERKIT_SERVICE_ID' => 'service_id',
        'WEATHERKIT_PRIVATE_KEY_FILE_PATH' => 'private_key_path',
        'default_language' => 'language',
        'default_timezone' => 'timezone',
        'default_country_code' => 'country_code',
        'default_datasets' => 'datasets',
        'default_daily_start' => 'daily_start',
        'default_daily_end' => 'daily_end',
        'default_hourly_start' => 'hourly_start',
        'default_hourly_end' => 'hourly_end',
        'locations_file_path' => 'locations_file',
        'export_dir' => 'export_dir',
    ];

    /** @var array<string, mixed> */
    private array $values;

    /**
     * @param array<string, mixed> $values
     *
     * @throws \InvalidArgumentException when a required value is missing or a key is unknown.
     */
    public function __construct(array $values)
    {
        $unknown = array_diff(array_keys($values), self::KNOWN);
        if ($unknown) {
            throw new \InvalidArgumentException('Unknown configuration key(s): ' . implode(', ', $unknown));
        }
        foreach (self::REQUIRED as $key) {
            if (!isset($values[$key]) || $values[$key] === '') {
                throw new \InvalidArgumentException("Missing required configuration value '$key'.");
            }
        }
        if (empty($values['private_key']) && empty($values['private_key_path'])) {
            throw new \InvalidArgumentException("Configuration needs either 'private_key' or 'private_key_path'.");
        }
        $this->values = $values;
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    /**
     * Loads a legacy `settings.php` file (which fills a `$settings` array) from the given directory.
     *
     * @throws \RuntimeException when the file is not readable.
     */
    public static function fromSettingsFile(string $directory): self
    {
        $file = rtrim($directory, '/\\') . '/settings.php';
        if (!is_readable($file)) {
            throw new \RuntimeException("Cannot read settings file: $file");
        }

        $load = static function (string $__file): array {
            $settings = [];
            $config = [];
            require $__file;
            return $settings;
        };

        $values = [];
        foreach ($load($file) as $legacyKey => $value) {
            if (isset(self::LEGACY_MAP[$legacyKey])) {
                $values[self::LEGACY_MAP[$legacyKey]] = $value;
            }
        }
        return new self($values);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * Returns the PEM contents of the private key.
     *
     * @throws \RuntimeException when the key file is missing, unreadable or empty.
     */
    public function privateKey(): string
    {
        if (!empty($this->values['private_key'])) {
            return (string) $this->values['private_key'];
        }
        $path = (string) $this->values['private_key_path'];
        if (!is_readable($path)) {
            throw new \RuntimeException("The private key file '$path' does not exist or is not readable.");
        }
        $key = file_get_contents($path);
        if ($key === false || trim($key) === '') {
            throw new \RuntimeException("The private key file '$path' is empty.");
        }
        return $key;
    }
}
