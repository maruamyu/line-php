<?php

namespace Maruamyu\Line\MessagingApi\Message;

/**
 * メッセージオブジェクト / テキストメッセージ
 *
 * @see https://developers.line.biz/ja/reference/messaging-api/#text-message
 */
class Text implements MessageInterface
{
    /** @var string */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = strval($text);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'text';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => 'text',
            'text' => strval($this->text),
        ];
    }
}
