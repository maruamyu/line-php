<?php

namespace Maruamyu\Line\MessagingApi\Message;

/**
 * メッセージオブジェクト / スタンプメッセージ
 *
 * @see https://developers.line.biz/ja/reference/messaging-api/#sticker-message
 */
class Sticker implements MessageInterface
{
    /** @var string */
    private $packageId;

    /** @var string */
    private $stickerId;

    /**
     * @param string $packageId
     * @param string $stickerId
     */
    public function __construct($packageId, $stickerId)
    {
        $this->packageId = strval($packageId);
        $this->stickerId = strval($stickerId);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'sticker';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => 'sticker',
            'packageId' => strval($this->packageId),
            'stickerId' => strval($this->stickerId),
        ];
    }
}
