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
     * Get extended information about an account.
     *
     * @param string $id the username to show
     *
     * @return array informations about the user
     */
    public function getAccount($id)
    {
        return $this->get('accounts/'.urlencode($id), [], ['type' => 'account']);
    }

    /**
     * Update account information.
     */
    public function update($id, array $data)
    {
    }

    /**
     * Claim an account for an app.
     * 
     * @param string $app         The app UUID or public ID
     * @param string $accountName /[a-z0-9]([a-z0-9\-]+[a-z0-9]|[a-z0-9]?)/ formatted account name.
     * @param string $email       The user claiming the account, will become the owner.
     * @param string $planId      The plan UUID or public ID
     * @param string $planType    The plan type, either trial or null.
     *
     * @return array The claimed account.
     */
    public function claim($app, $accountName, $email, $planId, $planType = 'trial')
    {
        return $this->post('apps/'.urlencode($app).'/actions/claim', [
            'account_name' => $accountName,
            'plan_id' => $planId,
            'plan_type' => $planType,
            'user_email' => $email,
        ], ['type' => 'account']);
    }

    /**
     * Get the accounts a user has access to.
     *
     * @param string $id the username
     *
     * @return array list of followed users
     */
    public function getUsers($id)
    {
        return $this->get('accounts/'.urlencode($id).'/users');
    }

    /**
     * Invite a user to an account with certain rights.
     *
     * @param string $accountId The id of the account
     * @param string $id        The users email address or UUID
     * @param array  $metadata  The metadata you want to attach to the user right
     *
     * @return array
     *
     * @throws HttpException
     */
    public function inviteUser($accountId, $id, $metadata = [])
    {
        return $this->post('accounts/'.urlencode($accountId).'/users', [
            'id' => $id,
            'metadata' => $metadata,
        ], ['type' => 'user-right']);
    }

    /**
     * Invite a user to an account with certain rights.
     *
     * @param string $accountId The id of the account
     * @param string $id        The users UUID 
     *
     * @throws HttpException
     */
    public function removeUser($accountId, $id)
    {
        $this->delete('accounts/'.urlencode($accountId).'/users/'.urlencode($id));
    }
}
