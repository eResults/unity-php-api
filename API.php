<?php

namespace Unity;

/**
 * Helper class for broker of single sign-on
 */
use Unity\Exceptions\BadRequest,
	Unity\Exceptions\Conflict,
	Unity\Exceptions\UnityException,
	Unity\Exceptions\Forbidden,
	Unity\Exceptions\NotFound,
	Unity\Exceptions\NotImplemented,
	Unity\Exceptions\SessionExpired,
	Unity\Exceptions\Unauthorized;

class API
{

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';

	/**
	 * Url of SSO server
	 * @var string
	 */
	private $serverUrl = 'https://id.eresults.nl';

	/**
	 * My identifier, given by Unity.
	 * @var string
	 */
	private $applicationKey;

	/**
	 * My private key, given by Unity.
	 * @var string
	 */
	private $privateKey;

	/**
	 * Users info recieved from the server.
	 * @var array
	 */
	private $users = array( );

	/**
	 * 
	 * @var array
	 */
	private $authorizedUser;

	/**
	 * Account info recieved from the server
	 * @var array
	 */
	private $accounts;

	/**
	 *
	 * @var string
	 */
	private $certificateLocation;

	/**
	 *
	 * @var string 
	 */
	private $domain;

	/**
	 * Class constructor
	 */
	public function __construct( $applicationKey, $privateKey, $domain, $serverUrl = null )
	{
		if ( !$applicationKey || !$privateKey )
		{
			throw new UnityException( 'Missing applicationKey or privateKey' );
		}
		$this->applicationKey = $applicationKey;
		$this->privateKey = $privateKey;

		$this->setDomain( $domain );

		if ( $serverUrl )
		{
			$this->serverUrl = $serverUrl;
		}

		$this->setCertificateLocation();
	}

	/**
	 * 
	 * @param string $location
	 */
	public function setCertificateLocation( $location = null )
	{
		if ( !$location )
		{
			$location = __DIR__ . '/ca-bundle.crt';
		}
		$this->certificateLocation = $location;
	}

	public function attach()
	{
		if ( !$this->getSessionAlias() )
		{
			header( 'Location: ' . $this->getAttachUrl( array( 
				'redirect' => $this->getUrl(), 
				'_domain' => $this->domain 
			)));
			exit();
		}
	}

	public function getSessionAlias()
	{
		if ( isset( $_GET['sessionAlias'] ) )
		{
			$url = parse_url( $this->getUrl() );

			$this->setSessionAlias( $_GET['sessionAlias'] );

			unset( $_GET['sessionAlias'] );

			$query = count( $_GET ) ? '?' : '';
			$query .= http_build_query( $_GET );

			header( 'Location: ' . str_replace( '?' . $url['query'], $query, $this->getUrl() ) );
			exit();
		}
		elseif ( isset( $_SESSION['unity']['sessionAlias'] ) )
		{
			return $_SESSION['unity']['sessionAlias'];
		}
		else
		{
			return false;
		}
	}

	private function getUrl()
	{
		return (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	}

	/**
	 * Login at sso server.
	 * 
	 * @param string $redirect url to redirect back to
	 */
	public function login( $redirect = null )
	{
		if ( !$redirect )
		{
			$redirect = $this->getUrl();
		}
		$this->redirect( 'sso', array( 
			'redirect' => $redirect, 
			'applicationKey' => $this->applicationKey, 
			'_domain' => $this->domain 
		));
	}

	/**
	 * Logout at sso server.
	 * @param string Redirect url. If false do no redirect.
	 * @param string AccountSystemName Default this is the currect account
	 */
	public function logout( $redirect = false )
	{
		$this->request( 'session', self::METHOD_DELETE, array( 'id' => $this->getSessionAlias() ) );

		if ( $redirect )
		{
			$this->redirect( 'sso', array(
				'redirect'	=> $redirect,
				'_domain'	=> $this->domain 
			));
		}
	}

	public function noAccess( $redirect = null )
	{
		if ( !$redirect )
		{
			$redirect = $this->getUrl();
		}
		$this->redirect( 'no-access', array(
			'redirect'	=> $redirect,
			'applicationKey' => $this->applicationKey
		));
	}

	private function redirect( $page, $params )
	{
		header( 'Location: ' . $this->serverUrl . '/' . $page . '?' . http_build_query( $params ) );
		exit();
	}

	/**
	 * Get URL to attach session at SSO server
	 *
	 * @return string
	 */
	public function getAttachUrl( $params = array( ) )
	{
		return $this->serverUrl . '/sso/attach?' . http_build_query( array_merge( array(
			'applicationKey' => $this->applicationKey
		), $params ) );
	}

	public function setDomain( $domain )
	{
		$this->domain = $domain;
		return $this;
	}

	/**
	 * Get an account
	 * 
	 * @param array $search array( id => 123, systemName = f4, name = test, domain = f4.usesmaia.com ) When empty return current account
	 * @return array
	 */
	public function getAccount( $search = array(), $refresh = false )
	{
		if ( isset( $search['systemName'] ) 
				&& isset( $this->accounts[ $search['systemName'] ] ) 
				&& !$refresh )
		{
			return $this->accounts[ $search['systemName'] ];
		}
		
		if ( !$search )
		{
			$search[ 'domain' ] = $this->domain;
		}

		$response = $this->request( 'account', self::METHOD_GET, $search );

		return $this->accounts[ $response['account']['systemName'] ] = $response['account'];
	}

	/**
	 * Get an user
	 * 
	 * @param array $search array( id => 123, email => test@test.nl ) It's required to provide one criteria
	 * @param boolean $refresh
	 * @return array
	 */
	public function getUser( $search, $refresh = false )
	{
		if ( isset( $search['id'] ) && isset( $this->users[ $search['id' ]] ) && !$refresh)
		{
			return $this->users[ $search['id'] ];
		}

		$response = $this->request( 'user', self::METHOD_GET, $search );
		return $this->users[$response['user']['id']] = $response['user'];
	}
	
	public function getUsers()
	{
		$response = $this->request( 'user' );
		return $response['users'];
	}

	/**
	 * Get the current user
	 * 
	 * @param boolean $refresh
	 * @return array
	 */
	public function getAuthorizedUser( $refresh = false )
	{
		if ( !isset( $this->authorizedUser ) || $refresh )
		{
			$response = $this->request( 'session', self::METHOD_GET, array( 'id' => $this->getSessionAlias() ) );
			$this->users[$response['user']['id']] = $response['user'];
			$this->authorizedUser = $response['user'];
		}
		return $this->authorizedUser;
	}

	/**
	 * Invite a user. The identifier can be an emailaddress or an user id.
	 * 
	 * @param string $identifier
	 * @param bool $role 'admin' or 'user'
	 * @return array
	 */
	public function inviteUser( $identifier, $role = 'user', $metadata = array() )
	{
		$data = array( strstr( $identifier, '@' ) ? 'email' : 'userId' => $identifier );
		
		$data['sessionAlias'] = $this->getSessionAlias();
		$data['role'] = $role;
		$data['metadata'] = json_encode( $metadata );

		$response = $this->request( 'user', self::METHOD_POST, $data );
		$this->users[$response['user']['id']] = $response['user'];
		return $response['user'];
	}

	/**
	 * Give an user a role.
	 * 
	 * @param string $user
	 * @param string $role 'admin' or 'user'
	 * @return array
	 */
	public function grantRights( $userId, $role = 'user', $metadata = array() )
	{
		$data = array (
			'id' => $userId,
			'role'	=> $role,
			'sessionAlias' => $this->getSessionAlias(),
			$data['metadata'] = json_encode( $metadata )
		);
		
		$response = $this->request( 'user', self::METHOD_PUT, $data );

		$this->users[$response['user']['id']] = $response['user'];
		return $response['user'];
	}

	public function removeUserFromAccount( $userId )
	{
		$data = array(
			'userId' => $userId,
			'sessionAlias' => $this->getSessionAlias()
		);
		$response = $this->request( 'user', self::METHOD_DELETE, $data );
		return $response['message'];
	}

	/**
	 * 
	 * Create an account
	 * The following keys are possible: productId, accountName, accountDisplayName, ( userId || ( email || firstName || lastName || password ) )
	 * @param array $data
	 */
	public function createAccount( $data )
	{
		// TODO: checken of alle verplichte waardes zijn meegegeven
		$response = $this->request( 'account', self::METHOD_POST, $data );

		$this->users[$response['user']['id']] = $response['user'];
		$this->accounts[$response['account']['systemName']] = $response['account'];

		return array(
			'account' => $response['account'],
			'user' => $response['user']
		);
	}

	/**
	 * Modify an account
	 * The following keys are possible: displayName, name
	 * 
	 * @param array $data
	 */
	public function modifyAccount( $data = array( ) )
	{
		$data['id'] = $this->applicationKey;

		$response = $this->request( 'account', self::METHOD_PUT, $data );

		$this->accounts[$response['account']['systemName']] = $response['account'];

		return $response['account'];
	}

	public function regenerateSessionAlias()
	{
		$response = $this->request( 'session', self::METHOD_PUT, array( 'id' => $this->getSessionAlias() ) );
		$this->setSessionAlias( $response['sessionAlias'] );
	}

	/**
	 * Execute on SSO server.
	 *
	 * @param string $cmd   Command
	 * @param array  $vars  Post variables
	 * @return array
	 */
	protected function request( $type, $method = self::METHOD_GET, $vars = array( ) )
	{
		$url = $this->serverUrl . '/api-v1/' . $type;

		$vars['unityHash'] = hash( 'sha256', $this->applicationKey . $this->privateKey );
		$vars['_domain'] = $this->domain;

		$curl = curl_init();
		
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );
		
		switch ( $method )
		{
			case self::METHOD_GET :
			case self::METHOD_PUT :
				if ( isset( $vars['id'] ) )
				{
					$id = '/' . $vars['id'];
					unset( $vars['id'] );
				}
				else
				{
					$id = '';
				}
				
				$url = $url . $id . '?' . http_build_query( $vars );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $vars );
				break;

			case self::METHOD_POST :
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $vars );
				break;

			case self::METHOD_DELETE :
				$url = $url . '?' . http_build_query( $vars );
				break;

			default :
				throw new \Exception( 'Invalid http method' );
		}

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt( $curl, CURLOPT_CAINFO, $this->certificateLocation );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 50 );

		$body = curl_exec( $curl );

		if ( curl_errno( $curl ) != 0 )
		{
			throw new UnityException( 'SSO failure: HTTP request to server failed. ' . curl_error( $curl ) );
		}

		if ( stristr( $body, 'Fatal error' ) )
		{
			throw new UnityException( 'Server returned "Fatal error"' );
		}

		$decodedBody = json_decode( $body, true );

		switch ( $decodedBody['code'] )
		{
			case '400' :
			case '500' :
				throw new BadRequest( $decodedBody['message'] );
			case '401' :
				throw new Unauthorized( $decodedBody['message'] );
			case '420' :
				throw new SessionExpired( $decodedBody['message'] );
			case '403' :
				throw new Forbidden( $decodedBody['message'] );
			case '404' :
				throw new NotFound( $decodedBody['message'] );
			case '409' :
				throw new Conflict( $decodedBody['message'] );
			case '501' :
				throw new NotImplemented( $decodedBody['message'] );
		}

		return $decodedBody;
	}

	/**
	 * 
	 * @param string $sessionAlias
	 */
	public function setSessionAlias( $sessionAlias )
	{
		$_SESSION['unity']['sessionAlias'] = $sessionAlias;
	}

	public function getServerUrl()
	{
		return $this->serverUrl;
	}

}
