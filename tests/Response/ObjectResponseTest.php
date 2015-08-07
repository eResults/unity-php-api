<?php

namespace eResults\Unity\Test\Api\Response;

use eResults\Unity\Api\Client,
	eResults\Unity\Api\Response\ObjectResponse,
	PHPUnit_Framework_TestCase;

class ObjectResponseTest
	extends PHPUnit_Framework_TestCase
{
	public function testFactory()
	{
		$this->assertInstanceOf( '\eResults\Unity\Api\Response\ObjectResponse', ObjectResponse::factory( new Client(), null ) );
		
		$this->assertInstanceOf( '\eResults\Unity\Api\Response\AccountResponse', ObjectResponse::factory(
			new Client(), 
			'account', [
				'_embedded' => [
					'users' => []
				]
			] )
		);
		
		$this->assertInstanceOf( '\eResults\Unity\Api\Response\UserResponse', ObjectResponse::factory( new Client(), 'user' ) );
	}
}