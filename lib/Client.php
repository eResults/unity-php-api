<?php

namespace eResults\Unity\Api;

class Client
{
	protected $options = [
		'token' => null,
		'client_id' => null,
		'client_secret' => null
	];

	protected $httpClient = null;
	
	/**
	 * The list of loaded API instances
	 *
	 * @var array
	 */
	protected $apis = array();

	public function __construct ( $options = [], HttpClient $client = null )
	{
		$this->options = array_merge( $this->options, $options );
		$this->httpClient = $client ?: new HttpClient\Curl( $this->options );
	}

	public function authenticate ( $token )
	{
		$this->getHttpClient()
			->setOption( 'token', $token );
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
		$response = $this->post('me/logout', [
			'return_to' => $returnTo
		] );
		
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
		return $this->getHttpClient()->get( $path, $parameters, $requestOptions );
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
		return $this->getHttpClient()->post( $path, $parameters, $requestOptions );
	}
}
