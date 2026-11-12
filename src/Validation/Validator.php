<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Validation;

use Oxy86\PhpWeatherKit\Parameters;
use Oxy86\PhpWeatherKit\Validation\Rules\Rule;
use Oxy86\PhpWeatherKit\Validation\Rules\LatitudeRule;
use Oxy86\PhpWeatherKit\Validation\Rules\LongitudeRule;
use Oxy86\PhpWeatherKit\Validation\Rules\LanguageRule;
use Oxy86\PhpWeatherKit\Validation\Rules\DatasetsRule;
use Oxy86\PhpWeatherKit\Validation\Rules\CountryCodeRule;
use Oxy86\PhpWeatherKit\Validation\Rules\TimeZoneRule;

use Oxy86\PhpWeatherKit\Exception\ValidationException;


/**
 * Creates a validator object to validate parameters according to Rules 
 * 
 * @see \Oxy86\PhpWeatherKit\Contracts\Validation\Rule
 */
class Validator
{
    /**
     * Validate the given parameters.
     *
     * @param  \Oxy86\PhpWeatherKit\Parameters  $parameters
     * @return void
     *
     * @throws ValidationException
     */
    public function validate(Parameters $parameters)
    {
        foreach ($this->validations($parameters) as $validation) {
            $this->validateParameterValue($validation['rule'], $validation['value']);
        }
    }

    /**
     * Get the parameters' validations.
     *
     * @param  \Oxy86\PhpWeatherKit\Parameters  $parameters
     * @return array
     */
    protected function validations(Parameters $parameters)
    {
        return [
            ['rule' => new LatitudeRule, 'value' => $parameters->getLatitude()],
            ['rule' => new LongitudeRule, 'value' => $parameters->getLongitude()],
            ['rule' => new LanguageRule, 'value' => $parameters->getLanguage()],
            ['rule' => new CountryCodeRule, 'value' => $parameters->getCountryCode()],
            ['rule' => new TimeZoneRule, 'value' => $parameters->getTimeZone()],
            ['rule' => new DatasetsRule, 'value' => $parameters->getDatasets()],
        ];
    }

    /**
     * Validate the given parameter value using the given rule.
     *
     * @param  \Oxy86\PhpWeatherKit\Contracts\Validation\Rule  $rule
     * @param  mixed  $value
     * @return void
     *
     * @throws ValidationException
     */
    protected function validateParameterValue(Rule $rule, $value)
    {
        if (!$rule->passes($value)) {
            throw new ValidationException($rule->message());
        }
    }
}
