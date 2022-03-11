<?php

namespace Statamic\Addons\Redirects;

class ManualRedirect
{
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var int
     */
    private $statusCode = 301;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var bool
     */
    private $retainQueryStrings = false;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function isRetainQueryStrings()
    {
        return $this->retainQueryStrings;
    }

    public function setRetainQueryStrings($retainQueryStrings)
    {
        $this->retainQueryStrings = $retainQueryStrings;

        return $this;
    }

    public function toArray()
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'status_code' => $this->statusCode,
            'locale' => $this->locale,
            'retain_query_strings' => $this->retainQueryStrings,
            'start_date' => $this->startDate instanceof \DateTime ? $this->startDate->format(self::DATE_TIME_FORMAT) : null,
            'end_date' => $this->endDate instanceof \DateTime ? $this->endDate->format(self::DATE_TIME_FORMAT) : null,
        ];
    }
}
