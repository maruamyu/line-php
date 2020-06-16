<?php

namespace Maruamyu\Line;

/**
 * LINE user profile object
 */
class UserProfile
{
    /** @var string */
    protected $displayName = '';

    /** @var string */
    protected $userId = '';

    /** @var string */
    protected $pictureUrl = '';

    /** @var string */
    protected $statusMessage = '';

    /**
     * @param array $response
     */
    public function __construct(array $response = null)
    {
        if (isset($response)) {
            if (isset($response['displayName'])) {
                $this->displayName = strval($response['displayName']);
            }
            if (isset($response['userId'])) {
                $this->userId = strval($response['userId']);
            }
            if (isset($response['pictureUrl'])) {
                $this->pictureUrl = strval($response['pictureUrl']);
            }
            if (isset($response['statusMessage'])) {
                $this->statusMessage = strval($response['statusMessage']);
            }
        }
    }

    /**
     * @return string
     */
    public function getdisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getuserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getpictureUrl()
    {
        return $this->pictureUrl;
    }

    /**
     * @return string
     */
    public function getstatusMessage()
    {
        return $this->statusMessage;
    }
}
