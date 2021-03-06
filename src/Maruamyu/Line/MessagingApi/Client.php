<?php

namespace Maruamyu\Line\MessagingApi;

use Maruamyu\Core\Http\Message\Uri;
use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\AuthorizationServerMetadata;
use Maruamyu\Core\OAuth2\Client as OAuth2Client;
use Maruamyu\Line\UserProfile;
use Psr\Http\Message\StreamInterface;

/**
 * LINE Messaging API client
 *
 * @see https://developers.line.biz/ja/reference/messaging-api/
 * @todo グループ, トークルーム, リッチメニュー
 */
class Client
{
    const API_ENDPOINT_ROOT = 'https://api.line.me/v2/';

    const MESSAGES_MAX_COUNT = 5;

    const MULTICAST_MAX_USERS = 150;

    /** @var string */
    protected $channelId;

    /** @var string */
    protected $channelSecret;

    /** @var OAuth2Client */
    protected $oAuth2Client;

    /**
     * @param string $channelId
     * @param string $channelSecret
     * @param AccessToken $accessToken
     */
    public function __construct($channelId, $channelSecret, AccessToken $accessToken = null)
    {
        $this->channelId = $channelId;
        $this->channelSecret = $channelSecret;

        $metadata = new AuthorizationServerMetadata();
        $metadata->tokenEndpoint = static::API_ENDPOINT_ROOT . 'oauth/accessToken';
        $metadata->revocationEndpoint = static::API_ENDPOINT_ROOT . 'oauth/revoke';
        # client credential into post body
        $metadata->supportedTokenEndpointAuthMethods = ['client_secret_post'];
        $metadata->supportedRevocationEndpointAuthMethods = ['client_secret_post'];

        $this->oAuth2Client = new OAuth2Client($metadata, $channelId, $channelSecret, $accessToken);
    }

    /**
     * @param string $replyToken
     * @param array $messages
     * @param bool $notificationDisabled
     * @return bool
     */
    public function sendReplyMessage($replyToken, array $messages, $notificationDisabled = false)
    {
        if (count($messages) < 1) {
            # throw new \InvalidArgumentException('messages is empty.');
            return false;
        }
        if (count($messages) > static::MESSAGES_MAX_COUNT) {
            # $errorMsg = 'too many messages. (max=' . static::MESSAGES_MAX_COUNT . ')';
            # throw new \InvalidArgumentException($errorMsg);
            return false;
        }

        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return false;
        }

        $data = [
            'replyToken' => $replyToken,
            'messages' => static::normalizeMessagesParameter($messages),
            'notificationDisabled' => $notificationDisabled,
        ];
        $requestBody = json_encode($data, JSON_UNESCAPED_SLASHES);

        $headers = [
            'Content-Type' => 'application/json',
            # 'X-Line-Signature' => $this->makeSignature($requestBody),
        ];
        $endpointUri = static::getEndpointUri('bot/message/reply');
        $response = $this->oAuth2Client->request('POST', $endpointUri, $requestBody, $headers);
        return $response->statusCodeIsOk();
    }

    /**
     * @param string $recipient
     * @param array $messages
     * @param bool $notificationDisabled
     * @return bool
     */
    public function sendPushMessage($recipient, array $messages, $notificationDisabled = false)
    {
        if (count($messages) < 1) {
            # throw new \InvalidArgumentException('messages is empty.');
            return false;
        }
        if (count($messages) > static::MESSAGES_MAX_COUNT) {
            # $errorMsg = 'too many messages. (max=' . static::MESSAGES_MAX_COUNT . ')';
            # throw new \InvalidArgumentException($errorMsg);
            return false;
        }

        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return false;
        }

        $data = [
            'to' => $recipient,
            'messages' => static::normalizeMessagesParameter($messages),
            'notificationDisabled' => $notificationDisabled,
        ];
        $requestBody = json_encode($data, JSON_UNESCAPED_SLASHES);

        $headers = [
            'Content-Type' => 'application/json',
            # 'X-Line-Signature' => $this->makeSignature($requestBody),
        ];
        $endpointUri = static::getEndpointUri('bot/message/push');
        $response = $this->oAuth2Client->request('POST', $endpointUri, $requestBody, $headers);
        return $response->statusCodeIsOk();
    }

    /**
     * @param string[] $userIdList
     * @param array $messages
     * @param bool $notificationDisabled
     * @return bool
     */
    public function sendMulticastMessage(array $userIdList, array $messages, $notificationDisabled = false)
    {
        if (count($userIdList) < 1) {
            # throw new \InvalidArgumentException('recipients is empty.');
            return false;
        }
        if (count($userIdList) > static::MULTICAST_MAX_USERS) {
            # $errorMsg = 'too many recipients. (max=' . static::MULTICAST_MAX_USERS . ')';
            # throw new \InvalidArgumentException($errorMsg);
            return false;
        }

        if (count($messages) < 1) {
            # throw new \InvalidArgumentException('messages is empty.');
            return false;
        }
        if (count($messages) > static::MESSAGES_MAX_COUNT) {
            # $errorMsg = 'too many messages. (max=' . static::MESSAGES_MAX_COUNT . ')';
            # throw new \InvalidArgumentException($errorMsg);
            return false;
        }

        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return false;
        }

        $data = [
            'to' => $userIdList,
            'messages' => static::normalizeMessagesParameter($messages),
            'notificationDisabled' => $notificationDisabled,
        ];
        $requestBody = json_encode($data, JSON_UNESCAPED_SLASHES);

        $headers = [
            'Content-Type' => 'application/json',
            # 'X-Line-Signature' => $this->makeSignature($requestBody),
        ];
        $endpointUri = static::getEndpointUri('bot/message/multicast');
        $response = $this->oAuth2Client->request('POST', $endpointUri, $requestBody, $headers);
        return $response->statusCodeIsOk();
    }

    /**
     * @param array $messages
     * @param bool $notificationDisabled
     * @return bool
     */
    public function sendBroadcastMessage(array $messages, $notificationDisabled = false)
    {
        if (count($messages) < 1) {
            # throw new \InvalidArgumentException('messages is empty.');
            return false;
        }
        if (count($messages) > static::MESSAGES_MAX_COUNT) {
            # $errorMsg = 'too many messages. (max=' . static::MESSAGES_MAX_COUNT . ')';
            # throw new \InvalidArgumentException($errorMsg);
            return false;
        }

        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return false;
        }

        $data = [
            'messages' => static::normalizeMessagesParameter($messages),
            'notificationDisabled' => $notificationDisabled,
        ];
        $requestBody = json_encode($data, JSON_UNESCAPED_SLASHES);

        $headers = [
            'Content-Type' => 'application/json',
            # 'X-Line-Signature' => $this->makeSignature($requestBody),
        ];
        $endpointUri = static::getEndpointUri('bot/message/multicast');
        $response = $this->oAuth2Client->request('POST', $endpointUri, $requestBody, $headers);
        return $response->statusCodeIsOk();
    }

    /**
     * @param string $messageId
     * @return StreamInterface|null binary data
     */
    public function fetchMessageContents($messageId)
    {
        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return null;
        }
        $endpointUri = static::getEndpointUri('bot/message/' . rawurlencode($messageId) . '/content');
        $response = $this->oAuth2Client->request('GET', $endpointUri);
        if ($response->statusCodeIsOk()) {
            return $response->getBody();
        } else {
            return null;
        }
    }

    /**
     * @return array|null ['type' => 'none|limited', 'value' => limited]
     */
    public function getQuota()
    {
        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return null;
        }
        $endpointUri = static::getEndpointUri('bot/message/quota');
        $response = $this->oAuth2Client->request('GET', $endpointUri);
        if ($response->statusCodeIsOk()) {
            $responseBody = strval($response->getBody());
            return json_decode($responseBody, true);
        } else {
            return null;
        }
    }

    /**
     * @return integer (return -1 if failed)
     */
    public function getQuotaTotalUsage()
    {
        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return null;
        }
        $endpointUri = static::getEndpointUri('bot/message/quota/consumption');
        $response = $this->oAuth2Client->request('GET', $endpointUri);
        if ($response->statusCodeIsOk()) {
            $responseBody = strval($response->getBody());
            $responseData = json_decode($responseBody, true);
            return intval($responseData['totalUsage']);
        } else {
            return -1;
        }
    }

    /**
     * @param string $date yyyyMMdd (UTC+09:00)
     * @return DeliveryStatus
     */
    public function getSentReplyMessagesDeliveryStatus($date)
    {
        $result = ['status' => 'failed'];
        $hasAccessToken = $this->reloadAccessToken();
        if ($hasAccessToken) {
            $endpointUri = static::getEndpointUri('bot/message/delivery/reply');
            $parameters = [
                'date' => strval($date),
            ];
            $response = $this->oAuth2Client->request('GET', $endpointUri->withQueryString($parameters));
            if ($response->statusCodeIsOk()) {
                $result = json_decode(strval($response->getBody()), true);
            }
        }
        return new DeliveryStatus($date, $result);
    }

    /**
     * @param string $date yyyyMMdd (UTC+09:00)
     * @return DeliveryStatus
     */
    public function getSentPushMessagesDeliveryStatus($date)
    {
        $result = ['status' => 'failed'];
        $hasAccessToken = $this->reloadAccessToken();
        if ($hasAccessToken) {
            $endpointUri = static::getEndpointUri('bot/message/delivery/push');
            $parameters = [
                'date' => strval($date),
            ];
            $response = $this->oAuth2Client->request('GET', $endpointUri->withQueryString($parameters));
            if ($response->statusCodeIsOk()) {
                $result = json_decode(strval($response->getBody()), true);
            }
        }
        return new DeliveryStatus($date, $result);
    }

    /**
     * @param string $date yyyyMMdd (UTC+09:00)
     * @return DeliveryStatus
     */
    public function getSentMulticastMessagesDeliveryStatus($date)
    {
        $result = ['status' => 'failed'];
        $hasAccessToken = $this->reloadAccessToken();
        if ($hasAccessToken) {
            $endpointUri = static::getEndpointUri('bot/message/delivery/multicast');
            $parameters = [
                'date' => strval($date),
            ];
            $response = $this->oAuth2Client->request('GET', $endpointUri->withQueryString($parameters));
            if ($response->statusCodeIsOk()) {
                $result = json_decode(strval($response->getBody()), true);
            }
        }
        return new DeliveryStatus($date, $result);
    }

    /**
     * @param string $date yyyyMMdd (UTC+09:00)
     * @return DeliveryStatus
     */
    public function getSentBroadcastMessagesDeliveryStatus($date)
    {
        $result = ['status' => 'failed'];
        $hasAccessToken = $this->reloadAccessToken();
        if ($hasAccessToken) {
            $endpointUri = static::getEndpointUri('bot/message/delivery/broadcast');
            $parameters = [
                'date' => strval($date),
            ];
            $response = $this->oAuth2Client->request('GET', $endpointUri->withQueryString($parameters));
            if ($response->statusCodeIsOk()) {
                $result = json_decode(strval($response->getBody()), true);
            }
        }
        return new DeliveryStatus($date, $result);
    }

    /**
     * @param string $userId
     * @return UserProfile|null
     */
    public function getUserProfile($userId)
    {
        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return null;
        }
        $endpointUri = static::getEndpointUri('bot/profile/' . rawurlencode($userId));
        $response = $this->oAuth2Client->request('GET', $endpointUri);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $result = json_decode(strval($response->getBody()), true);
        return new UserProfile($result);
    }

    /**
     * @param string $userId
     * @return string linkToken (empty if failed)
     */
    public function requestLinkToken($userId)
    {
        $hasAccessToken = $this->reloadAccessToken();
        if (!$hasAccessToken) {
            return '';
        }
        $endpointUri = static::getEndpointUri('bot/user/' . rawurlencode($userId) . '/linkToken');
        $response = $this->oAuth2Client->request('POST', $endpointUri);
        if ($response->statusCodeIsOk() == false) {
            return '';
        }
        $result = json_decode(strval($response->getBody()), true);
        return strval($result['linkToken']);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->oAuth2Client->setAccessToken($accessToken);
    }

    /**
     * @return AccessToken|null
     */
    public function getAccessToken()
    {
        return $this->oAuth2Client->getAccessToken();
    }

    /**
     * @return AccessToken|null
     */
    public function reloadAccessToken()
    {
        if ($this->oAuth2Client->hasValidAccessToken()) {
            return $this->oAuth2Client->getAccessToken();
        }
        try {
            return $this->oAuth2Client->requestClientCredentialsGrant();
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $body
     * @param string $signature
     * @return bool
     */
    public function verifySignature($body, $signature)
    {
        return ($signature === $this->makeSignature($body));
    }

    /**
     * @param string $body
     * @return string base64
     */
    protected function makeSignature($body)
    {
        return base64_encode(hash_hmac('sha256', $body, $this->channelSecret, true));
    }

    /**
     * @param string $path
     * @return Uri
     */
    protected static function getEndpointUri($path)
    {
        return new Uri(static::API_ENDPOINT_ROOT . $path);
    }

    /**
     * @param Message\MessageInterface[]|array[] $messages
     * @return array[]
     */
    protected static function normalizeMessagesParameter(array $messages)
    {
        $converted = [];
        foreach ($messages as $message) {
            if ($message instanceof Message\MessageInterface) {
                $converted[] = $message->toArray();
            } elseif (is_array($message)) {
                $converted[] = $message;
            } else {
                # throw new \RuntimeException('invalid message type');
                continue;
            }
        }
        return $converted;
    }
}
