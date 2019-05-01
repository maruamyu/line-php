<?php

namespace Maruamyu\Line\SocialApi;

use Maruamyu\Core\Http\Message\Uri;
use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\Client as OAuth2Client;
use Maruamyu\Core\OAuth2\Settings as OAuth2Settings;
use Maruamyu\Line\UserProfile;

/**
 * LINE login OAuth2 client
 *
 * @see https://developers.line.biz/ja/reference/social-api/
 */
class Client extends OAuth2Client
{
    const API_ENDPOINT_ROOT = 'https://api.line.me/';

    /**
     * @param string $channelId
     * @param string $channelSecret
     * @param AccessToken $accessToken
     */
    public function __construct($channelId, $channelSecret, AccessToken $accessToken = null)
    {
        $oAuth2Settings = new OAuth2Settings();
        $oAuth2Settings->clientId = $channelId;
        $oAuth2Settings->clientSecret = $channelSecret;
        $oAuth2Settings->authorizationEndpoint = 'https://access.line.me/oauth2/v2.1/authorize';
        $oAuth2Settings->tokenEndpoint = static::API_ENDPOINT_ROOT . 'oauth2/v2.1/token';
        $oAuth2Settings->revocationEndpoint = static::API_ENDPOINT_ROOT . 'oauth2/v2.1/revoke';
        $oAuth2Settings->isRequiredClientCredentialsOnRevocationRequest = true;
        $oAuth2Settings->isUseBasicAuthorizationOnClientCredentialsRequest = false;

        parent::__construct($oAuth2Settings, $accessToken);
    }

    /**
     * @return UserProfile|null
     */
    public function getProfile()
    {
        if (!($this->accessToken)) {
            # throw new \RuntimeException('access_token not set yet.');
            return null;
        }
        $endpointUri = static::getEndpointUri('v2/profile');
        $response = $this->request('GET', $endpointUri);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $result = json_decode(strval($response->getBody()), true);
        return new UserProfile($result);
    }

    /**
     * @return boolean
     */
    public function hasFriendship()
    {
        if (!($this->accessToken)) {
            # throw new \RuntimeException('access_token not set yet.');
            return false;
        }
        $endpointUri = static::getEndpointUri('friendship/v1/status');
        $response = $this->request('GET', $endpointUri);
        if ($response->statusCodeIsOk() == false) {
            return false;
        }
        $buffer = strval($response->getBody());
        $result = json_decode($buffer, true);
        return (isset($result['friendFlag']) && $result['friendFlag']);
    }

    /**
     * access token info
     */
    public function checkHoldingAccessToken()
    {
        if (!($this->accessToken)) {
            return null;
        }
        $parameters = [
            'access_token' => $this->accessToken->getToken(),
        ];
        $endpointUri = static::getEndpointUri('oauth2/v2.1/verify');
        $response = $this->httpClient->request('GET', $endpointUri->withQueryString($parameters));
        $buffer = strval($response->getBody());
        $tokenData = json_decode($buffer, true);

        if (strcmp($this->settings->clientId, $tokenData['client_id']) != 0) {
            # throw new \RuntimeException('client_id not match!!');
            return null;
        }

        $this->accessToken->update($tokenData);
        return $this->getAccessToken();
    }

    /**
     * revoke access token
     *
     * @return boolean
     * @throws \Exception if invalid settings
     */
    public function revokeAccessToken()
    {
        if (!($this->accessToken)) {
            return false;
        }
        # token_type_hint not required
        return $this->requestTokenRevocation($this->accessToken->getToken(), '');
    }

    /**
     * @param string $path
     * @return Uri
     */
    protected static function getEndpointUri($path)
    {
        return new Uri(static::API_ENDPOINT_ROOT . $path);
    }
}
