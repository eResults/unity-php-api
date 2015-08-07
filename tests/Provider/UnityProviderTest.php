<?php

namespace eResults\Unity\Test\Api;

use eResults\Unity\Api\Provider\UnityProvider,
	PHPUnit_Framework_TestCase;

class UnityProviderTest
	extends PHPUnit_Framework_TestCase
{
	
	public function testType()
	{
		$this->assertInstanceOf('League\OAuth2\Client\Provider\AbstractProvider', new UnityProvider());
	}
	
}