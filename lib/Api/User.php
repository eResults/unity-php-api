<?php

namespace eResults\Unity\Api\Api;

use eResults\Unity\Api\Api;

/**
 * Searching users, getting user information
 * and managing authenticated user account information.
 */
class User extends Api
{
    /**
     * Get extended information about a user by its username.
     *
     * @param string $id the username to show
     *
     * @return array informations about the user
     */
    public function getUser($id)
    {
        return $this->get('users/'.urlencode($id), [], ['type' => 'user']);
    }

    /**
     * Update user informations.
     *
     * @param string $id   the username to update
     * @param array  $data key=>value user attributes to update.
     *                     key can be name, email, blog, company or location
     *
     * @return array informations about the user
     */
    public function update($id, array $data)
    {
    }

    /**
     * Get the accounts a user has access to.
     *
     * @param string $id the username
     *
     * @return array list of followed users
     */
    public function getAccounts($id)
    {
        return $this->get('users/'.urlencode($id).'/accounts');
    }
}
