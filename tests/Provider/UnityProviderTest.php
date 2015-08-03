<?php

namespace eResults\Unity\Test\Api;

use eResults\Unity\Api\Provider\UnityProvider,
	League\OAuth2\Client\Provider\AbstractProvider,
	PHPUnit_Framework_TestCase;

class UnityProviderTest
	extends PHPUnit_Framework_TestCase
{
	
	public function testType()
	{
		$this->assertInstanceOf(AbstractProvider::class, new UnityProvider());
	}
	
}