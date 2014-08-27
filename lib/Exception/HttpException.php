<?php

namespace eResults\Unity\Api\Exception;

/**
 * Description of HttpException
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
	
	public function __construct ( $code, array $json )
	{
		parent::__construct( $json['error_description'], $code );
		
		$this->json = $json;
	}
	
	public function getError ()
	{
		return $this->json['error'];
	}
}
