<?php

namespace Oxy86\PhpWeatherKit\Validation\Rules;

use Oxy86\PhpWeatherKit\Validation\Rules\Rule;

class LatitudeRule implements Rule
{
    /**
     * {@inheritdoc}
     */
    public function passes($value)
    {
        return is_numeric($value)
            && ($value >= -90)
            && ($value <= +90);
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return 'The given latitude is invalid.';
    }
}
