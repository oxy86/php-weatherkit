<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Weather;

use function Oxy86\PhpWeatherKit\array_get;

class Headers
{
    /**
     * The headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Create a new instance of the weather response headers.
     *
     * @param  array  $headers
     * @return void
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }


    /**
     * The conservative value for data caching purposes, based on the data present in the response body.
     *
     * @return array
     */
    public function cacheControl()
    {
        return array_get($this->headers, 'Cache-Control', []);
    }

    /**
     * The date
     *
     * @return array
     */
    public function date()
    {
        return array_get($this->headers, 'Date', []);
    }


    /**
     * Get an array representation of the headers.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->headers;
    }
}
