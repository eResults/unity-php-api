<?php

namespace eResults\Unity\Api\Provider;

use League\OAuth2\Client\Provider\IdentityProvider,
	League\OAuth2\Client\Token\AccessToken;

/**
 * Description of UnityOAuthProvider
 *
 * @author niels
 */
class UnityProvider
	extends IdentityProvider
{
	protected $baseUrl = 'https://api.eresults.nl';
	
	public function urlAccessToken()
	{
		return $this->baseUrl . '/oauth/v2/token';
	}

	public function urlAuthorize()
	{
		return $this->baseUrl . '/oauth/v2/auth';
	}

	public function urlUserDetails( AccessToken $token )
	{
		return $this->baseUrl . '/api/me?access_token=' . $token;
	}

	public function userDetails( $response, AccessToken $token )
	{
		return (array) $response;
	}
}
