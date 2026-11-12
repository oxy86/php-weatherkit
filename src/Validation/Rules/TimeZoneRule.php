<?php

namespace Oxy86\PhpWeatherKit\Validation\Rules;

use Oxy86\PhpWeatherKit\Validation\Rules\Rule;


class TimeZoneRule implements Rule
{

  /**
   * {@inheritdoc}
   */
  public function passes($value)
  {
    return !empty($value) && is_string($value)
      && in_array($value, self::supportedTimezones());
  }

  /**
   * {@inheritdoc}
   */
  public function message()
  {
    return 'The given timezone is invalid.';
  }


  /**
   * Get the supported timezones.
   *
   * @return array
   */
  public static function supportedTimezones()
  {
    return  \DateTimeZone::listIdentifiers(
        \DateTimeZone::ALL
    );
  }
}
