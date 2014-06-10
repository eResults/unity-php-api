<?php

namespace eResults\Unity\Api\Api;

use eResults\Unity\Api\Api;

/**
 * Searching accounts, getting account information
 * and managing basic account information.
 */
class Account
	extends Api
{
    /**
     * Get extended information about a user by its username
     *
     * @param   string  $id         the username to show
     * @return  array                     informations about the user
     */
    public function get ( $id )
    {
        return $this->get('accounts/' . urlencode( $id ));
    }

    /**
     * Update user informations.
     *
     * @param   string  $id         the username to update
     * @param   array   $data             key=>value user attributes to update.
     *                                    key can be name, email, blog, company or location
     * @return  array                     informations about the user
     */
    public function update( $id, array $data )
    {
    }

    /**
     * Get the accounts a user has access to
     *
     * @param   string  $id         the username
     * @return  array                     list of followed users
     */
    public function getUsers( $id )
    {
        return $this->get('users/' . urlencode( $id ) . '/users');
    }
	
	/**
     * Invite a user to an account with certain rights.
     *
     * @param   string  $id         the username
     * @return  array                     list of followed users
     */
	public function inviteUser ( $accountId, $email, $role = 'user', $metadata = array() )
	{
		return $this->post( 'users/' . urlencode( $accountId ) . '/users', array(
			'user' => array(
				'email' => $email
			),
			
			'right' => array(
				'metadata' => $metadata,
				'role' => $role
			)
		) );
	}
	
	/**
     * Invite a user to an account with certain rights.
     *
     * @param   string  $id         the username
     * @return  array                     list of followed users
     */
	public function removeUser ( $accountId, $id )
	{
		return $this->delete( 'users/' . urlencode( $accountId ) . '/users/' . urlencode( $id ) );
	}
}