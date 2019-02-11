<?php

namespace eResults\Unity\Api\Api;

use eResults\Unity\Api\Api;

/**
 * Searching users, getting user information
 * and managing authenticated user account information.
 */
class Certificate extends Api
{
    /**
     * Get the validation information for a certificate
     *
     * @param string $id id of the certificate
     *
     * @return array validation information about the certificate
     */
    public function getValidation($id)
    {
        return $this->get('certificates/'.urlencode($id).'/validation');
    }


    /**
     * Get the information for a certificate
     *
     * @param string $id id of the certificate
     *
     * @return array certificate
     */
    public function getCertificate($id)
    {
        return $this->get('certificates/'.urlencode($id));
    }
}
