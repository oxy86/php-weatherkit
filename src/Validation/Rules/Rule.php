<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Validation\Rules;

interface Rule
{
    /**
     * Determines if the validation rule passes.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function passes($value);

    /**
     * Gets the validation error message.
     *
     * @return string
     */
    public function message();
}
