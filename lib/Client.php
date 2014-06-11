<?php

namespace eResults\Unity\Api;

use Guzzle\Http\Client as HttpClient,
	Guzzle\Http\Message\Request,
	Guzzle\Http\Message\Response;

class Client
{
	protected $options = array(
		'token' => null,
		'client_id' => null,
		'client_secret' => null,
		
		'protocol'	=> 'https',
		'url'		=> ':protocol://id.eresults.nl/api/',
		'userAgent'	=> 'php-eresults-api (http://eresults.nl/api)',
		'httpPort'  => 443,
		'timeout'	=> 10,
		'token'		=> null,
		'format'	=> 'json'
	);

	/**
	 *
	 * @var HttpClient
	 */
	protected $httpClient = null;
	
	/**
	 * The list of loaded API instances
	 *
	 * @var array
	 */
	protected $apis = array();

	public function __construct ( $options = array(), HttpClient $client = null )
	{
		$this->options = array_merge( $this->options, $options );
		
		$url = strtr( $this->options['url'], array(
			':protocol' => $this->options['protocol'],
			':format'   => $this->options['format']
		) );

		$this->httpClient = $client ?: new HttpClient( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->options['token']
			)
		) );
	}

	public function authenticate ( $token )
	{
		var_dump( $this->httpClient->getConfig()->get('headers') );
//		$this->getHttpClient()
//			->setOption( 'token', $token );
//			->setOption( 'client_id', $clientId )
//			->setOption( 'client_secret', $clientSecret );
	}

	public function getOption ( $option )
	{
		return isset( $this->options[ $option ] )
			? $this->options[ $option ]
			: null;
	}
	
	public function getHttpClient ()
	{
		return $this->httpClient;
	}
	
	public function getAuthenticatedUser ()
	{
		return $this->get('me');
	}
	
	public function logout ( $returnTo = null )
	{
		$response = $this->post('me/logout', array(
			'return_to' => $returnTo
		) );
		
		header("Location: {$response['logout_url']}");
		die();
	}
	
	/**
	 * Get the user API
	 *
	 * @return  Api\User
	 */
	public function getUserApi()
	{
		if ( !isset( $this->apis['user'] ) )
			$this->apis['user'] = new Api\User( $this );

		return $this->apis['user'];
	}
	
	/**
	 * Get the account API
	 *
	 * @return  Api\User
	 */
	public function getAccountApi()
	{
		if ( !isset( $this->apis['account'] ) )
			$this->apis['account'] = new Api\Account( $this );

		return $this->apis['account'];
	}
	
	/**
	 * Inject an API instance
	 *
	 * @param   string	$name		The API name
	 * @param   Api		$instance	The API instance
	 *
	 * @return  Api	
	 */
	public function setApi( $name, Api $instance )
	{
		$this->apis[ $name ] = $instance;

		return $this;
	}

	/**
	 * Get any API
	 *
	 * @param   string	$name	The API name
	 * @return  Api		
	 */
	public function getApi( $name )
	{
		return $this->apis[ $name ];
	}

	/**
	 * Call any path, GET method
	 * Ex: $api->get('me')
	 *
	 * @param   string  $path			 the Api path
	 * @param   array   $parameters	   GET parameters
	 * @param   array   $requestOptions   reconfigure the request
	 * @return  array
	 */
	public function get( $path, array $parameters = array(), $requestOptions = array() )
	{
		return $this->handleRequest( $this->getHttpClient()->get( $path, $parameters, $requestOptions ) );
	}

	/**
	 * Call any path, POST method
	 * Ex: $api->post('user/[user-id]')
	 *
	 * @param   string  $path			 the Api path
	 * @param   array   $parameters	   POST parameters
	 * @param   array   $requestOptions   reconfigure the request
	 * @return  array
	 */
	public function post( $path, array $parameters = array(), $requestOptions = array() )
	{
		return $this->handleRequest( $this->getHttpClient()->post( $path, $parameters, $requestOptions ) );
	}

	/**
	 * Call any path, DELETE method
	 * Ex: $api->delete('user/[user-id]')
	 *
	 * @param   string  $path				the Api path
	 * @param   array   $parameters			DELETE parameters
	 * @param   array   $requestOptions		reconfigure the request
	 * @return  array
	 */
	public function delete( $path, array $parameters = array(), $requestOptions = array() )
	{
		return $this->handleRequest( $this->getHttpClient()->delete( $path, $parameters, $requestOptions ) );
	}
	
	public function handleRequest ( Request $request )
	{
		$request->addHeader( 'Authorization', 'Bearer ' . $this->options['token'] );
		
		try
		{
			$response = $request->send();
		}
		catch ( \Guzzle\Http\Exception\ClientErrorResponseException $ex )
		{
			$this->handleResponse( $ex->getResponse() );
		}
		
		return $this->handleResponse( $response );
	}
	
	public function handleResponse ( Response $response )
	{
		$body = $response->json();
		
		if( !preg_match( '~[23][0-9]{2}~', $response->getStatusCode() ) )
			throw new Exception\HttpException( $response->getStatusCode(), $body['error'] );
			
		if( isset( $body['pages'] ) && ctype_digit( $body['pages'] ) )
			return new PaginatedCollection( $body );
		
		return $body;
	}
}
