<?php

namespace eResults\Unity\Api\Response;

/**
 * Description of UserResponse
 *
 * @author niels
 */
class AccountResponse
	extends ObjectResponse
{
	public function build ()
	{
		parent::build();

		if( isset( $this->data['claimant'] ) )
			$this->data['claimant'] = new UserResponse( $this->client, $this->data[] );
		
		foreach( $this->embedded['users'] as &$user )
			$user = new UserRightResponse( $this->client, $user );
	}
}
