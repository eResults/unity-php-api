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

    public function __construct(array $rawData = [], array $options = [])
    {
        $this->rightData = $rawData['right'];

        parent::__construct(array_merge($rawData['user'], [
            'user' => new UserResponse($rawData['user'], $options)
        ]), $options);
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
