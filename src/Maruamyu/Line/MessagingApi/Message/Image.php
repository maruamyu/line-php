<?php

namespace Maruamyu\Line\MessagingApi\Message;

/**
 * メッセージオブジェクト / 画像メッセージ
 *
 * @see https://developers.line.biz/ja/reference/messaging-api/#image-message
 */
class Image implements MessageInterface
{
    /** @var string */
    private $originalContentUrl;

    /** @var string */
    private $previewImageUrl;

    /**
     * @param string $originalContentUrl
     * @param string $previewImageUrl
     */
    public function __construct($originalContentUrl, $previewImageUrl)
    {
        $this->originalContentUrl = strval($originalContentUrl);
        $this->previewImageUrl = strval($previewImageUrl);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'image';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => 'image',
            'originalContentUrl' => strval($this->originalContentUrl),
            'previewImageUrl' => strval($this->previewImageUrl),
        ];
    }
}
