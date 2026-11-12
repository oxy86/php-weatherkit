<?php

namespace Oxy86\PhpWeatherKit\Http;



/**
 * Creates a Request object
 */
class Request
{
    /**
     * The Request ID.
     *
     * @var string|null
     */
    protected $id;

    /**
     * The URL.
     *
     * @var string
     */
    protected $url;

    /**
     * The query string.
     *
     * @var string
     */
    protected $query;


    /**
     * The JWT.
     *
     * @var string|null
     */
    protected $jwt;

    

    /**
     * Create a new instance of the request.
     *
     * @param  string  $url
     * @param  string  $query
     * @param  string|null  $id
     * @return void
     */
    public function __construct($url, $query, $id = null, $jwt= null)
    {
        $this->id = $id;
        $this->url = $url;
        $this->query = $query;
        $this->jwt = $jwt;

    }

    /**
     * Gets the ID.
     *
     * @return string|null
     */
    public function id()
    {
        return $this->id;
    }


    /**
     * Gets the JWT of the request.
     *
     * @return string|null
     */
    public function jwt()
    {
        return $this->jwt;
    }


    /**
     * Gets the URL of the request
     *
     * @return string
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * Gets the query string of the request
     *
     * @return string
     */
    public function query()
    {
        return $this->query;
    }
}
