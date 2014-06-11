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

	/**
	 * Get the League auth provider for easy authentication.
	 * 
	 * @param array $options A set of options for the Provider
	 * @return Provider\UnityProvider
	 */
	public function getAuthProvider ( $options = array() )
	{
		$options = array_merge( $options, array(
			'clientId' => $this->options['client_id'],
			'clientSecret' => $this->options['client_secret']
		) );
		
		return new Provider\UnityProvider( $options );
	}
	
	/**
	 * Get the value of an option.
	 * 
	 * @param string $option
	 * @return mixed
	 */
	public function getOption ( $option )
	{
		return isset( $this->options[ $option ] )
			? $this->options[ $option ]
			: null;
	}
	
	/**
	 * Set an option.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return Client
	 */
	public function setOption ( $key, $value )
	{
		$this->options[ $key ] = $value;
		return $this;
	}
	
	/**
	 * Get the Guzzle HttpClient
	 * 
	 * @return HttpClient
	 */
	public function getHttpClient ()
	{
		return $this->httpClient;
	}
	
	/**
	 * A shorthand to get the currently authenticated user.
	 * 
	 * @return array
	 * @throws HttpException
	 */
	public function getAuthenticatedUser ()
	{
		return $this->get('me');
	}
	
	/**
	 * Shorthand to logout the currently authenticated user.
	 * 
	 * @param string $returnTo The URL to which the user will be returned after logging out, or when the user decides to cancel.
	 * @throws HttpException
	 */
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
	 * @return Api\User
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
	 * @return Api\User
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
	
	/**
	 * Add authentication data to the request and sends it. Calls handleResponse when done.
	 * 
	 * @param \Guzzle\Http\Message\Request $request
	 * @return mixed
	 */
	protected function handleRequest ( Request $request )
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
	
	/**
	 * Handle the Guzzle request response. Returns either an array or a PaginatedCollection.
	 * 
	 * @param \Guzzle\Http\Message\Response $response
	 * @return array|PaginatedCollection
	 * @throws Exception\HttpException
	 */
	protected function handleResponse ( Response $response )
	{
		$body = $response->json();
		
		if( !preg_match( '~[23][0-9]{2}~', $response->getStatusCode() ) )
			throw new Exception\HttpException( $response->getStatusCode(), $body['error'] );
			
		if( isset( $body['pages'] ) && ctype_digit( $body['pages'] ) )
			return new Collection\PaginatedCollection( $body );
		
		return $body;
	}
}
