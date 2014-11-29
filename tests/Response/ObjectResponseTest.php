<?php

namespace eResults\Unity\Test\Api\Response;

use eResults\Unity\Api\Client;
use eResults\Unity\Api\Response\AccountResponse;
use eResults\Unity\Api\Response\ObjectResponse;
use eResults\Unity\Api\Response\UserResponse;

class ObjectResponseTest
	extends \PHPUnit_Framework_TestCase
{
	public function testFactory()
	{
		$this->assertInstanceOf( ObjectResponse::class, ObjectResponse::factory( new Client(), null ) );
		
		$this->assertInstanceOf( AccountResponse::class, ObjectResponse::factory(
			new Client(), 
			'account', [
				'_embedded' => [
					'users' => []
				]
			] )
		);
		
		$this->assertInstanceOf( UserResponse::class, ObjectResponse::factory( new Client(), 'user' ) );
	}
}