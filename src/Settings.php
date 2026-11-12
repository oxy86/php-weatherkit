<?php

namespace Oxy86\PhpWeatherKit;

use Oxy86\PhpWeatherKit\Utility\DebugUtils;


/**
 * Stores read-only settings from settings.php.
 */
final class Settings {

  /**
   * Array with the settings.
   *
   * @var array
   */
  private $storage = [];

  /**
   * Singleton instance.
   *
   * @var \Oxy86\PhpWeatherKit\Settings
   */
  private static $instance = NULL;


  /**
   * Constructor.
   *
   * @param array $settings
   *   Array with the settings.
   */
  public function __construct(array $settings) {
    $this->storage = $settings;
    self::$instance = $this;
  }

  /**
   * Returns the settings instance.
   *
   * A singleton is used because this class is used before the container is
   * available.
   *
   * @return \Oxy86\PhpWeatherKit\Settings
   *
   * @throws \BadMethodCallException
   *   Thrown when the settings instance has not been initialized yet.
   */
  public static function getInstance() {
    if (self::$instance === NULL) {
      throw new \BadMethodCallException('Settings::$instance is not initialized yet. Whatever you are trying to do, it might be too early for that. You could call Settings::initialize(), but it is probably better to wait until it is called in the regular way. Also check for recursions.');
    }
    return self::$instance;
  }

  /**
   * Protects creating with clone.
   */
  private function __clone() {
  }

    /**
   * Prevents settings from being serialized.
   */
  public function __sleep() {
    throw new \LogicException('Settings can not be serialized. This probably means you are serializing an object that has an indirect reference to the Settings object. Adjust your code so that is not necessary.');
  }

  /**
   * Returns a setting.
   *
   * Settings can be set in settings.php in the `$settings` array and requested
   * by this function. Settings should be used over configuration for read-only,
   * possibly low bootstrap configuration that is environment specific.
   *
   * @param string $name
   *   The name of the setting to return.
   * @param mixed $default
   *   (optional) The default value to use if this setting is not set.
   *
   * @return mixed
   *   The value of the setting, the provided default if not set.
   */
  public static function get($name, $default = NULL) {
    return self::$instance->storage[$name] ?? $default;
  }

  /**
   * Returns all the settings. This is only used for testing purposes.
   *
   * @return array
   *   All the settings.
   */
  public static function getAll() {
    return self::$instance->storage;
  }

  /**
   * Gets settings from settings.php and bootstraps the Settings singleton
   *
   * @param string $app_root
   *   The app root where the settings.php file must be present.
   *
   * @see settings.php.example
   * 
   * @throws RuntimeException
   *   In case the settings.php file does not exist or not readable.
   */
  public static function initialize($app_root) {

    // Export configuration variables from the `$config` array of settings.php to the global namespace.
    global $config;
    $config = [];

    // This is where settings will be stored temporarily before stored in the static storage
    $settings = [];

    $settings_file = $app_root . '/settings.php';
    if (is_readable($settings_file)) {
      require $settings_file;
    } else {
      throw new \RuntimeException("Cannot read settings file: $settings_file. Check file...");
    }

    DebugUtils::e(__METHOD__, "Settings file $settings_file is readable. Creating object to store settings in static storage...");

    // Initialize Settings.
    new Settings($settings);
  }

}