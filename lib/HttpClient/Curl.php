<?php

namespace eResults\Unity\Api\HttpClient;

use eResults\Unity\Api\HttpClient;

/**
 * Performs requests on the eResults Api.
 *
 * @author	Niels Janssen <nielsjanssen@eresults.nl>
  */
class Curl
	extends HttpClient
{
	/**
	 * Send a request to the server, receive a response
	 *
	 * @param  string   $path		  Request url
	 * @param  array	$parameters	Parameters
	 * @param  string   $httpMethod	HTTP method to use
	 * @param  array	$options	   Request options
	 *
	 * @return string   HTTP response
	 */
	public function doRequest($url, array $parameters = [], $httpMethod = 'GET', array $options = [])
	{
		$curlOptions = [];

		$options['headers'] = array_merge( [
			'Authorization' => 'Bearer ' . $options['token']
		], $options['headers'] ?: array() );

		if( $httpMethod === 'POST' )
		{
			$curlOptions += [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => array()
			];
		}
		
		if ( !empty($parameters) )
		{
			switch( $httpMethod )
			{
				case 'GET':
					$url .= '?' . utf8_encode( http_build_query($parameters, '', '&') );
					break;
				
				case 'POST':
				default:
					$curlOptions[ CURLOPT_POSTFIELDS ] = $body = json_encode( $parameters );
					
					$options['headers'] = array_merge( $options['headers'], [
						'Content-Type' => 'application/json',
						'Content-Length' => strlen( $body )
					] );
			}
		}

		foreach( $options['headers'] as $key => &$value )
			$value = $key . ': ' . $value;
		
		$curlOptions += [
			CURLOPT_URL => $url,
			CURLOPT_PORT => $options['httpPort'],
			CURLOPT_USERAGENT => $options['userAgent'],
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_TIMEOUT => $options['timeout'],
			CURLOPT_HTTPHEADER => $options['headers']
		];

		$response = $this->doCurlCall( $curlOptions );
		$responseBody = $this->decodeResponse( $response['response'], [] );
		
		if ( !in_array( $response['headers']['http_code'], array( 0, 200, 201 ) ) )
			throw new Exception(
				isset( $responseBody['error'] )
					? $responseBody['error']
					: null,
				(int) $response['headers']['http_code']
			);
		
		if ( $response['errorNumber'] != '' )
			throw new Exception('error ' . $response['errorNumber']);

		return $responseBody;
	}

	protected function doCurlCall( array $curlOptions )
	{
		$curl = curl_init();

		curl_setopt_array( $curl, $curlOptions );

		$response = curl_exec( $curl );
		$headers = curl_getinfo( $curl );
		$errorNumber = curl_errno( $curl );
		$errorMessage = curl_error( $curl );

		curl_close($curl);

		return compact('response', 'headers', 'errorNumber', 'errorMessage');
	}
}