<?php

namespace eResults\Unity\Api\Response;

use eResults\Unity\Api\Client;

/**
 * Description of UserRightResponse.
 *
 * @author niels
 */
class UserRightResponse
    extends UserResponse
{
    protected $rightData = [];
    protected $metadata;

    public function __construct(Client $client, array $rawData = [])
    {
        $this->rightData = $rawData['right'];

        parent::__construct($client, [
            'user' => new UserResponse($rawData['user'])
        ]);
    }

    public function build()
    {
        parent::build();

        $this->metadata = $this->rightData['metadata'];
    }

    public function getMetadata($key = null, $default = null)
    {
        if (!isset($this->metadata)) {
            return null;
        }

        return $key === null
            ? $this->metadata
            : (isset($this->metadata[ $key ])
                ? $this->metadata[ $key ]
                : $default);
    }
}
