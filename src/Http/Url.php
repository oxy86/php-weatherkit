<?php

namespace Oxy86\PhpWeatherKit\Http;

/**
 * Creates a Url object.
 */
class Url
{
    /**
     * The URL.
     *
     * @var string
     */
    protected $value;


    /**
     * Create a new instance of the URL.
     *
     * @param  string  $value
     * 
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;

    }

    /**
     * Get the URL.
     *
     * @return string
     */
    public function value()
    {
        return $this->value;
    }

}
