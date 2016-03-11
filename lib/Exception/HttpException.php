<?php

namespace eResults\Unity\Api\Exception;

/**
 * Description of HttpException.
 *
 * @author niels
 */
class HttpException
    extends \Exception
{
    /**
     * The JSON error body.
     * 
     * @var array
     */
    protected $json = array();

    public function __construct($code, array $json)
    {
        $message = '';
        
        if (isset($json['error_description'])) {
            $message = $json['error_description'];
        } elseif (isset($json['message'])) {
            $message = $json['message'];
        }
        
        parent::__construct($message, $code);

        $this->json = $json;
    }

    public function getError()
    {
        return $this->json['error'];
    }
}
