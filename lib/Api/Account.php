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
     * Get extended information about an account
     *
     * @param   string  $id         the username to show
     * @return  array                     informations about the user
     */
    public function getAccount ( $id )
    {
        return $this->get('accounts/' . urlencode( $id ));
    }

    /**
     * Update account information.
     *
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
        return $this->get('accounts/' . urlencode( $id ) . '/users');
    }
	
	/**
     * Invite a user to an account with certain rights.
     *
	 * @param	string	$accountId	The id of the account
     * @param	string	$id			The users email address or UUID
	 * @param	array	$metadata	The metadata you want to attach to the user right
     * @return	array
	 * @throws HttpException
     */
	public function inviteUser ( $accountId, $id, $metadata = array() )
	{
		return $this->post( 'accounts/' . urlencode( $accountId ) . '/users', array(
			'id' => $id,
			'metadata' => $metadata
		) );
	}
	
	/**
     * Invite a user to an account with certain rights.
     *
     * @param string	$accountId	The id of the account
	 * @param string	$id			The users email address or UUID 
	 * @throws HttpException
     */
	public function removeUser ( $accountId, $id )
	{
		$this->delete( 'users/' . urlencode( $accountId ) . '/users/' . urlencode( $id ) );
	}
}