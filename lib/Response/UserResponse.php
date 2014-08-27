<?php

namespace eResults\Unity\Api\Response;

/**
 * Description of UserResponse
 *
 * @author niels
 */
class UserResponse
	extends ObjectResponse
{
	public function hasRole ( $role )
	{
		return $this->get('role') === $role;
	}
}
