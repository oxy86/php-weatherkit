<?php

declare(strict_types=1);

namespace Oxy86\PhpWeatherKit\Weather;

use function Oxy86\PhpWeatherKit\array_get;

/**
 * A severe weather alert (WeatherKit `WeatherAlertSummary`).
 *
 * @see https://developer.apple.com/documentation/weatherkitrestapi/weatheralertsummary
 */
class Alert
{
    /**
     * The alert data.
     *
     * @var array
     */
    protected $alert;

    /**
     * Create a new instance of the weather alert.
     *
     * @param  array  $alert
     * @return void
     */
    public function __construct(array $alert)
    {
        $this->alert = $alert;
    }

    /** A unique identifier of the alert. */
    public function id()
    {
        return array_get($this->alert, 'id');
    }

    /** A human-readable description of the alert. */
    public function description()
    {
        return array_get($this->alert, 'description');
    }

    /** The name of the affected area. */
    public function areaName()
    {
        return array_get($this->alert, 'areaName');
    }

    /** The official identifier of the affected area. */
    public function areaId()
    {
        return array_get($this->alert, 'areaId');
    }

    /** The ISO 3166 country code of the alert. */
    public function countryCode()
    {
        return array_get($this->alert, 'countryCode');
    }

    /** The severity: extreme, severe, moderate, minor or unknown. */
    public function severity()
    {
        return array_get($this->alert, 'severity');
    }

    /** The type of weather event (e.g. wind, thunderstorm). */
    public function phenomenon()
    {
        return array_get($this->alert, 'phenomenon');
    }

    public function significance()
    {
        return array_get($this->alert, 'significance');
    }

    /** The urgency: immediate, expected, future, past or unknown. */
    public function urgency()
    {
        return array_get($this->alert, 'urgency');
    }

    /** The certainty: observed, likely, possible, unlikely or unknown. */
    public function certainty()
    {
        return array_get($this->alert, 'certainty');
    }

    /** The importance: high, normal or low. */
    public function importance()
    {
        return array_get($this->alert, 'importance');
    }

    /** The recommended responses (array of strings). */
    public function responses()
    {
        return array_get($this->alert, 'responses', []);
    }

    /** The name of the issuing authority. */
    public function source()
    {
        return array_get($this->alert, 'source');
    }

    /** The time the alert was issued (ISO 8601). */
    public function issuedTime()
    {
        return array_get($this->alert, 'issuedTime');
    }

    /** The time the alert takes effect (ISO 8601). */
    public function effectiveTime()
    {
        return array_get($this->alert, 'effectiveTime');
    }

    /** The time the alert expires (ISO 8601). */
    public function expireTime()
    {
        return array_get($this->alert, 'expireTime');
    }

    public function eventOnsetTime()
    {
        return array_get($this->alert, 'eventOnsetTime');
    }

    public function eventEndTime()
    {
        return array_get($this->alert, 'eventEndTime');
    }

    /** A URL with more details about the alert. */
    public function detailsUrl()
    {
        return array_get($this->alert, 'detailsUrl');
    }

    /** Attribution URL for the alert source. */
    public function attributionUrl()
    {
        return array_get($this->alert, 'attributionURL');
    }

    /**
     * Get an array representation of the alert.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->alert;
    }
}
