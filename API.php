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
	private $accountSystemName;

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
	 * @var Zend_Session_Namespace
	 */
	private $session;
	private $certificateLocation;

	/**
	 * Class constructor
	 */
	public function __construct( $accountSystemName, $privateKey, $serverUrl = null, $auto_attach = true )
	{
		if ( !$accountSystemName || !$privateKey )
		{
			throw new UnityException( 'Missing accountSystemName or privateKey' );
		}
		$this->accountSystemName = $accountSystemName;
		$this->privateKey = $privateKey;

		if ( $serverUrl !== null )
		{
			$this->serverUrl = $serverUrl;
		}

		$this->session = new \Zend_Session_Namespace( 'unity' );

		$this->setCertificateLocation();

		if ( $auto_attach )
		{
			$this->attach();
		}
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

	public function attach( $force = false )
	{
		if ( $force || !$this->getSessionAlias() )
		{
			header( 'Location: ' . $this->getAttachUrl( array( 'redirect' => $this->getUrl() ) ) );
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
		elseif ( isset( $this->session->sessionAlias ) )
		{
			return $this->session->sessionAlias;
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
		$this->redirect( 'sso', $redirect, $this->accountSystemName );
	}

	/**
	 * Logout at sso server.
	 * @param string Redirect url. If false do no redirect.
	 * @param string AccountSystemName Default this is the currect account
	 */
	public function logout( $redirect = false, $accountSystemName = false )
	{
		$this->request( 'session', self::METHOD_DELETE, array( 'id' => $this->getSessionAlias() ) );

		if ( $redirect )
		{
			if ( !$accountSystemName )
			{
				$accountSystemName = $this->accountSystemName;
			}
			$this->redirect( 'sso', $redirect, $accountSystemName );
		}
	}

	public function noAccess( $redirect = null )
	{
		if ( !$redirect )
		{
			$redirect = $this->getUrl();
		}
		$this->redirect( 'no-access', $redirect, $this->accountSystemName );
	}

	private function redirect( $page, $redirect, $accountSystemName )
	{
		header( 'Location: ' . $this->serverUrl . '/' . $page . '?' . http_build_query( array( 'redirect' => $redirect, 'accountSystemName' => $accountSystemName ) ) );
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
							'accountSystemName' => $this->accountSystemName
								), $params ) );
	}

	/**
	 * 
	 * Enter description here ...
	 * @return array
	 */
	public function getAccount( $accountSystemName = null, $refresh = false )
	{
		if ( !$accountSystemName )
		{
			$accountSystemName = $this->accountSystemName;
		}
		if ( !isset( $this->accounts[$accountSystemName] ) || $refresh )
		{
			$response = $this->request( 'account', self::METHOD_GET, array( 'id' => $accountSystemName ) );
			$this->accounts[$accountSystemName] = $response['account'];
		}
		return $this->accounts[$accountSystemName];
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
	 * 
	 * Enter description here ...
	 * @param string $identifier
	 * @return array
	 */
	public function inviteUser( $identifier )
	{
		if ( strstr( $identifier, '@' ) )
		{
			$data = array( 'email' => $identifier );
		}
		else
		{
			$data = array( 'userId' => $identifier );
		}
		$data['sessionAlias'] = $this->getSessionAlias();

		$response = $this->request( 'user', self::METHOD_POST, $data );
		$this->users[$response['user']['id']] = $response['user'];
		return $response['user'];
	}

	public function removeUserFromAccount( $userId )
	{
		$data = array(
			'userId' => $userId,
			'sessionAlias' => $this->getSessionAlias()
		);
		$this->request( 'user', self::METHOD_DELETE, $data );
	}

	/**
	 * 
	 * Create an account
	 * The following keys are possible: productId, accountName, ( userId || ( userEmail || userFirstName || userLastName || userPassword ) )
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
		$data['id'] = $this->accountSystemName;

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

		$vars['unityHash'] = hash( 'sha256', $this->accountSystemName . $this->privateKey );
		$vars['accountSystemName'] = $this->accountSystemName;

		switch ( $method )
		{
			case self::METHOD_GET :
				if ( isset( $vars['id'] ) )
				{
					$id = '/' . $vars['id'];
					unset( $vars['id'] );
				}
				else
				{
					$id = '';
				}
				$curl = curl_init( $url . $id . '?' . http_build_query( $vars ) );
				break;

			case self::METHOD_POST :
				$curl = curl_init( $url );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $vars );
				break;

			case self::METHOD_PUT :
				$curl = curl_init( $url . '/' . $vars['id'] );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $vars );
				break;

			case self::METHOD_DELETE :
				$curl = curl_init( $url );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $vars );
				break;

			default :
				throw new \Exception( 'Invalid http method' );
		}

		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );
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

		$decodedBody = \Zend_Json::decode( $body );

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
		$this->session->sessionAlias = $sessionAlias;
	}

	public function getServerUrl()
	{
		return $this->serverUrl;
	}

}
