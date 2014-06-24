<?php

namespace eResults\Unity\Api\Response;

use eResults\Unity\Api\Client;

/**
 * Description of ResponseObject
 *
 * @author niels
 */
class ObjectResponse
	implements \ArrayAccess
{
	/**
	 * @var Client
	 */
	protected $client;
	
	/**
	 *
	 * @var array
	 */
	protected $data = [];
	
	/**
	 *
	 * @var array
	 */
	protected $embedded = [];
	
	public function __construct ( Client $client, array $rawData = [] )
	{
		$this->client = $client;
		$this->data = $rawData;
				
		if( isset( $this->data['_embedded'] ) )
			$this->parseEmbedded( $this->data['_embedded'] );
	}
	
	public function build () {}
	
	public function factory ( $client, $type = null, $data = [] )
	{
		switch( $type )
		{
			case 'account':
				return new AccountResponse( $client, $data );
				
			case 'user':
				return new UserResponse( $client, $data );
			
			default:
				return new self( $client, $data );
		}
	}

	protected function parseEmbedded ( $embedded )
	{
		foreach( $embedded as $name => $values )
		{
			$this->embedded[ $name ] = [];
			
			foreach( $values as $key => $value )
				$this->embedded[ $name ][ $key ] = ResponseObject::factory ( $this->client, $value );
		}
	}
	
	public function get ( $key )
	{
		return isset( $this->data[ $key ] )
			? $this->data[ $key ]
			: ( isset( $this->embedded[ $key ] )
				? $this->embedded[ $key ]
				: null );
	}
	
	public function getLink ( $name )
	{
		if( !isset( $this->data['_links'][ $name ] ) )
			return null;
		
		$link = $this->data['_links'][ $name ];

		return preg_match( '|^https?\:|', $link )
			? $link
			: strtr( $this->client->getOption('url'), [ ':path' => $link ] );
	}
	
	public function offsetExists($offset)
	{
		return isset( $this->data[ $offset ] );
	}

	public function offsetGet($offset)
	{
		return isset( $this->data[ $offset ] )
			? $this->data[ $offset ]
			: null;
	}

	public function offsetSet($offset, $value)
	{
		$this->data[ $offset ] = $value;
	}

	public function offsetUnset($offset)
	{
		unset( $this->data[ $offset ] );
	}
}
