<?php

namespace Maruamyu\Line\MessagingApi\Message;

/**
 * メッセージオブジェクト インタフェース
 */
interface MessageInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function toArray();
}
