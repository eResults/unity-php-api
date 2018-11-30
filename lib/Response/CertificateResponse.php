<?php

namespace eResults\Unity\Api\Response;

/**
 * Description of UserResponse.
 *
 * @author niels
 */
class CertificateResponse
    extends ObjectResponse
{
    public function isPending()
    {
        return $this->get('status') === 'pending';
    }
}
