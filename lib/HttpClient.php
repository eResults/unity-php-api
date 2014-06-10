<?php

namespace eResults\Unity\Api;

/**
 * Performs requests on the eResults Api.
 *
 * @author    Niels Janssen <nielsjanssen@eresults.nl>
  */
abstract class HttpClient
{
    /**
     * The http client options
     * @var array
     */
    protected $options = array(
        'protocol'	=> 'https',
        'url'		=> ':protocol://id.eresults.nl/api/:path',
        'userAgent'	=> 'php-eresults-api (http://eresults.nl/api)',
        'httpPort'  => 443,
        'timeout'	=> 10,
        'token'		=> null
    );

    /**
     * Instanciate a new http client
     *
     * @param  array   $options  http client options
     */
    public function __construct( array $options = array() )
    {
        $this->options = array_merge( $this->options, $options );
    }

    /**
     * Send a request to the server, receive a response
     *
     * @param  string   $url           Request url
     * @param  array    $parameters    Parameters
     * @param  string   $httpMethod    HTTP method to use
     * @param  array    $options        Request options
     *
     * @return string   HTTP response
     */
    abstract protected function doRequest( $url, array $parameters = array(), $httpMethod = 'GET', array $options = array() );

    /**
     * Send a GET request
     *
     * @param  string   $path            Request path
     * @param  array    $parameters     GET Parameters
     * @param  string   $httpMethod     HTTP method to use
     * @param  array    $options        Request options
     *
     * @return array                    Data
     */
    public function get( $path, array $parameters = array(), array $options = array() )
    {
        return $this->request( $path, $parameters, 'GET', $options );
    }

    /**
     * Send a POST request
     *
     * @param  string   $path            Request path
     * @param  array    $parameters     POST Parameters
     * @param  string   $httpMethod     HTTP method to use
     * @param  array    $options        reconfigure the request for this call only
     *
     * @return array                    Data
     */
    public function post( $path, array $parameters = array(), array $options = array() )
    {
        return $this->request( $path, $parameters, 'POST', $options );
    }

    /**
     * Send a DELETE request
     *
     * @param  string   $path			Request path
     * @param  array    $parameters		DELETE Parameters
     * @param  string   $httpMethod		HTTP method to use
     * @param  array    $options		reconfigure the request for this call only
     *
     * @return array                    Data
     */
    public function delete( $path, array $parameters = array(), array $options = array() )
    {
        return $this->request( $path, $parameters, 'DELETE', $options );
    }

    /**
     * Send a request to the server, receive a response,
     * decode the response and returns an associative array
     *
     * @param  string   $path           Request Api path
     * @param  array    $parameters     Parameters
     * @param  string   $httpMethod     HTTP method to use
     * @param  array    $options        Request options
     *
     * @return array                    Data
     */
    public function request( $path, array $parameters = array(), $httpMethod = 'GET', array $options = array() )
    {
        $options = array_merge( $this->options, $options );

        // create full url
        $url = strtr( $options['url'], array(
            ':protocol' => $options['protocol'],
            ':format'   => $options['format'],
            ':path'     => trim($path, '/')
        ) );

        // get encoded response
        return $this->doRequest( $url, $parameters, $httpMethod, $options );
    }

    /**
     * Get a JSON response and transform it to a PHP array
     *
     * @return  array   the response
     */
    protected function decodeResponse( $response, array $options )
    {
        return json_decode($response, true);
    }

    /**
     * Change an option value.
     *
     * @param string $name   The option name
     * @param mixed  $value  The value
     *
     * @return HttpClient The current object instance
     */
    public function setOption( $name, $value )
    {
        $this->options[$name] = $value;

        return $this;
    }
}