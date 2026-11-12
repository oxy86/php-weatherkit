<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Weather;

use function Oxy86\PhpWeatherKit\array_get;


/**
 * Creates a container to hold weather metadata
 */
class Metadata
{
  /**
   * The metadata.
   *
   * @var array
   */
  protected $metadata;

  /**
   * Create a new instance of the weather metadata.
   *
   * @param  array  $metadata
   * @return void
   */
  public function __construct(array $metadata)
  {
    $this->metadata = $metadata;
  }

  /**
   * The URL of the legal attribution for the data source.
   *
   * @return string|null
   */
  public function attributionURL()
  {
    return array_get($this->metadata, 'attributionURL');
  }

  /**
   *  The time when the weather data is no longer valid.
   *
   * @return string|null
   */
  public function expireTime()
  {
    return array_get($this->metadata, 'expireTime');
  }

  /**
   * The ISO language code for localizable fields.
   *
   * @return string|null
   */
  public function language()
  {
    return array_get($this->metadata, 'language');
  }



  /**
   * The latitude of the relevant location.
   *
   * @return number|null
   */
  public function latitude()
  {
    return array_get($this->metadata, 'latitude');
  }

  /**
   * The longitude of the relevant location.
   *
   * @return number|null
   */
  public function longitude()
  {
    return array_get($this->metadata, 'longitude');
  }


  /**
   * The URL of a logo for the data provider.
   *
   * @return string|null
   */
  public function providerLogo()
  {
    return array_get($this->metadata, 'providerLogo');
  }


  /**
   * The name of the data provider.
   *
   * @return string|null
   */
  public function providerName()
  {
    return array_get($this->metadata, 'providerName');
  }



  /**
   *  The time the weather data was procured.
   *
   * @return string|null
   */
  public function readTime()
  {
    return array_get($this->metadata, 'readTime');
  }


  /**
   *  The time the provider reported the weather data.
   *
   * @return string|null
   */
  public function reportedTime()
  {
    return array_get($this->metadata, 'reportedTime');
  }



  /**
   *  The weather data is temporarily unavailable from the provider.
   *
   * @return bool
   */
  public function temporarilyUnavailable()
  {
    return array_get($this->metadata, 'temporarilyUnavailable');
  }



  /**
   *  The system of units that the weather data is reported in. This is set to metric.
   *
   * @return string|null
   */
  public function units()
  {
    return array_get($this->metadata, 'units');
  }


  /**
   * Get an array representation of the metadata
   *
   * @return array
   */
  public function toArray()
  {
    return $this->metadata;
  }
}
