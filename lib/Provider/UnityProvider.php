<?php

namespace eResults\Unity\Api\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Description of UnityOAuthProvider.
 *
 * @author niels
 */
class UnityProvider
    extends AbstractProvider
{
    protected $baseUri = 'https://api.eresults.nl/';

    public function urlAccessToken()
    {
        return $this->baseUri.'oauth/v2/token';
    }

    public function urlAuthorize()
    {
        return $this->baseUri.'oauth/v2/auth';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return $this->baseUri.'api/me?access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        return (array) $response;
    }
}
