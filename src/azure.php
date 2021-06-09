<?php


namespace storfollo\ad_update;


use adtools;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;

class azure
{
    /**
     * @var \TheNetworg\OAuth2\Client\Provider\Azure
     */
    public $provider;
    /**
     * @var adtools
     */
    public $adtools;

    function __construct($options = ['clientId'=>'', 'clientSecret'=>'', 'redirectUri'=>''])
    {
        $this->provider = new \TheNetworg\OAuth2\Client\Provider\Azure($options);
        $baseGraphUri = $this->provider->getBaseAuthorizationUrl();
        $this->provider->scope = 'openid profile email offline_access ' . $baseGraphUri . '/User.Read';
    }

    function getLocalUser($token, $fields = ['dn']) {
        $resourceOwner = $this->provider->getResourceOwner($token);
        $upn = $resourceOwner->getUpn();
        return $this->adtools->find_object($upn, false, 'upn', $fields);
    }

    /**
     * @param AccessToken $token
     * @return AccessToken
     * @throws IdentityProviderException
     */
    function checkToken($token)
    {
        /*try {
            $this->me($token);
        } catch (IdentityProviderException $e) {
            //echo $e->getMessage()."\n";
            return null;
        }*/

        if ($token->hasExpired()) {
            if (!is_null($token->getRefreshToken())) {
                $token = $this->provider->getAccessToken('refresh_token', [
                    'scope'         => $this->provider->scope,
                    'refresh_token' => $token->getRefreshToken()
                ]);
            } else {
                $token = null;
            }
        }
        return $token;
    }

    /**
     * @param $token
     * @throws IdentityProviderException
     */
    function me($token)
    {
        return $this->provider->get('http://graph.microsoft.com/v1.0/me/messages', $token);
    }

    function redirectToLogin() {
        $authorizationUrl = $this->provider->getAuthorizationUrl(['scope' => $this->provider->scope]);
        $_SESSION['OAuth2.state'] = $this->provider->getState();
        header('Location: ' . $authorizationUrl);
    }
}