<?php

namespace Maruamyu\Line\SocialApi;

use Maruamyu\Core\Http\Message\Uri;
use Maruamyu\Core\OAuth2\AccessToken;
use Maruamyu\Core\OAuth2\OpenIDProviderMetadata;
use Maruamyu\Line\UserProfile;

/**
 * LINE login OAuth2 client
 *
 * @see https://developers.line.biz/ja/reference/social-api/
 */
class Client extends \Maruamyu\Core\OAuth2\Client
{
    const API_ENDPOINT_ROOT = 'https://api.line.me/';

    /**
     * @param string $channelId
     * @param string $channelSecret
     * @param AccessToken $accessToken
     */
    public static function createInstance($channelId, $channelSecret, AccessToken $accessToken = null)
    {
        $issuer = 'https://access.line.me';
        # $metadata = static::fetchOpenIDProviderMetadata($issuer);
        $metadataValues = [
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer . '/oauth2/v2.1/authorize',
            'token_endpoint' => static::API_ENDPOINT_ROOT . 'oauth2/v2.1/token',
            'jwks_uri' => static::API_ENDPOINT_ROOT . 'oauth2/v2.1/certs',
            'response_types_supported' => ['code'],
            'subject_types_supported' => ['pairwise'],
            'id_token_signing_alg_values_supported' => ['ES256'],
        ];
        $metadata = new OpenIDProviderMetadata($metadataValues);
        $metadata->revocationEndpoint = static::API_ENDPOINT_ROOT . 'oauth2/v2.1/revoke';
        # client credential into post body
        $metadata->supportedTokenEndpointAuthMethods = ['client_secret_post'];
        $metadata->supportedRevocationEndpointAuthMethods = ['client_secret_post'];

        return new static($metadata, $channelId, $channelSecret, $accessToken);
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
        $response = $this->getHttpClient()->request('GET', $endpointUri->withQueryString($parameters));
        $buffer = strval($response->getBody());
        $tokenData = json_decode($buffer, true);

        if (strcmp($this->clientId, $tokenData['client_id']) != 0) {
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
        $request = $this->makeTokenRevocationRequest($this->accessToken->getToken());
        $response = $this->getHttpClient()->send($request);
        if ($response->statusCodeIsOk()) {
            $this->accessToken = null;
            return true;
        } else {
            return false;
        }
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
