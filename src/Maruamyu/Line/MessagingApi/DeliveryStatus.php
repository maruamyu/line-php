<?php

namespace Maruamyu\Line\MessagingApi;

/**
 * message delivery status
 */
class DeliveryStatus
{
    /** @var string */
    private $date;

    /** @var string */
    private $status;

    /** @var string */
    private $successfulCount;

    /**
     * @param string $date yyyyMMdd (UTC+09:00)
     * @param array $response API response
     */
    public function __construct($date, array $response)
    {
        $this->date = strval($date);
        $this->status = strval($response['status']);
        if (isset($response['success'])) {
            $this->successfulCount = intval($response['success'], 10);
        } else {
            $this->successfulCount = 0;
        }
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return integer
     */
    public function getSuccessfulCount()
    {
        return $this->successfulCount;
    }
}
