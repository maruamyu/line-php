<?php

namespace Maruamyu\Line;

/**
 * LINE user profile object
 */
class UserProfile
{
    /** @var string */
    protected $displayName;

    /** @var string */
    protected $userId;

    /** @var string */
    protected $pictureUrl;

    /** @var string */
    protected $statusMessage;

    /**
     * @param array $response
     */
    public function __construct(array $response = null)
    {
        if (isset($response)) {
            $this->displayName = strval($response['displayName']);
            $this->userId = strval($response['userId']);
            $this->pictureUrl = strval($response['pictureUrl']);
            $this->statusMessage = strval($response['statusMessage']);
        } else {
            $this->displayName = '';
            $this->userId = '';
            $this->pictureUrl = '';
            $this->statusMessage = '';
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
