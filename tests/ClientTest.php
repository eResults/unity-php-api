<?php

namespace eResults\Unity\Test;

class ClientTest
	extends \PHPUnit_Framework_TestCase
{
	
	public function testAuthProvider()
	{
		$client = new \eResults\Unity\Api\Client();

		$this->assertInstanceOf(\eResults\Unity\Api\Provider\UnityProvider::class, $client->getAuthProvider());
	}
	
}