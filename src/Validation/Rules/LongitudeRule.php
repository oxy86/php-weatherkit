<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Validation\Rules;

use Oxy86\PhpWeatherKit\Validation\Rules\Rule;

class LongitudeRule implements Rule
{

  /**
   * {@inheritdoc}
   */
  public function passes($value)
  {
    return is_numeric($value)
      && ($value >= -180)
      && ($value <= +180);
  }

  /**
   * {@inheritdoc}
   */
  public function message()
  {
    return 'The given longitude is invalid.';
  }
}
