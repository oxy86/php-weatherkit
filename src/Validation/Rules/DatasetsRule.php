<?php

namespace Oxy86\PhpWeatherKit\Validation\Rules;

use Oxy86\PhpWeatherKit\Parameters;
use Oxy86\PhpWeatherKit\Parameters\Datasets;
use Oxy86\PhpWeatherKit\Validation\Rules\Rule;


class DatasetsRule implements Rule
{

    /**
     * {@inheritdoc}
     */
    public function passes($value)
    {

        if (is_null($value) || empty($value)) {
            return true;
        }

        $included = is_array($value) ? $value : [$value];

        $supported_datasets = Parameters::getSupportedDatasets();

        foreach ($included as $key => $dataset) {
            if (!in_array($dataset, $supported_datasets)) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return 'The dataset name is not supported by Php WeatherKit.';
    }
}
