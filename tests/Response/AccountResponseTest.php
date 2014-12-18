<?php

namespace eResults\Unity\Test\Api\Response;

use eResults\Unity\Api\Client;
use eResults\Unity\Api\Response\ObjectResponse;

class AccountResponseTest
	extends \PHPUnit_Framework_TestCase
{
	public function testClaimant()
	{
		$response = ObjectResponse::factory(
			new Client(), 
			'account', 
			[ 
				'claimant' => [
					'name' => 'Niels',
					'role' => 'admin'
				],
				'_embedded' => [
					'users' => []
				]
			]
		);

		$this->assertInstanceOf( \eResults\Unity\Api\Response\UserResponse::class, $response->get('claimant') );
		
		$this->assertTrue( $response->get('claimant')->hasRole('admin') );
	}
	
	public function testUsers()
	{
		$response = ObjectResponse::factory(
			new Client(), 
			'account', 
			[ 
				'_embedded' => [
					'users' => [
						[
							'user' => [
								'name' => 'Kees'
							],
							'right' => [
								'metadata' => 'meta'
							]
						],
						[
							'user' => [
								'name' => 'Jan',
							],
							'right' => [
								'metadata' => 'meta'
							]
						]
					]
				]
			]
		);
		
		$users = $response->get('users');
		
		$this->assertEquals( 2, count( $users ));
		
		$jan = array_pop( $users );
		
		$this->assertEquals( 'Jan', $jan->get('name') );
	}
}