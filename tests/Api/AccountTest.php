<?php

namespace eResults\Unity\Test\Api;

class AccountTest
	extends \PHPUnit_Framework_TestCase
{
	
	public function testConstruct()
	{
		$client = new \eResults\Unity\Api\Client();
		$this->assertInstanceOf(\eResults\Unity\Api\Api\Account::class, new \eResults\Unity\Api\Api\Account($client));
	}
	
}