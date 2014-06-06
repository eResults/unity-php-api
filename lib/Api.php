<?php

namespace eResults\Unity\Api;

/**
 * Abstract class for Api classes
 */
abstract class Api
{
    /**
     * The client
	 * 
     * @var Client
     */
    private $client;

    public function __construct( Client $client )
    {
        $this->client = $client;
    }

    /**
     * Call any path, GET method
     *
     * @param   string  $path				the path
     * @param   array   $parameters			GET parameters
     * @param   array   $requestOptions		reconfigure the request
     * @return  array
     */
    protected function get( $path, array $parameters = [], array $requestOptions = [] )
    {
        return $this->client->get( $path, $parameters, $requestOptions );
    }

    /**
     * Call any path, POST method
     *
     * @param   string  $path				the path
     * @param   array   $parameters			POST parameters
     * @param   array   $requestOptions		reconfigure the request
     * @return  array
     */
    protected function post( $path, array $parameters = [], array $requestOptions = [] )
    {
        return $this->client->post( $path, $parameters, $requestOptions );
    }
}
