<?php

namespace eResults\Unity\Api\Grant;

use League\OAuth2\Client\Grant\GrantInterface,
	\League\OAuth2\Client\Token\AccessToken;

/**
 * Description of ClientCredentials
 *
 * @author niels
 */
class ClientCredentials
	implements GrantInterface
{
	public function __toString()
	{
		return 'client_credentials';
	}

	public function handleResponse($response = array())
	{
		return new AccessToken( $response );
	}

	public function prepRequestParams( $defaultParams, $params )
	{
		return array_merge( $defaultParams, $params );
	}
}
