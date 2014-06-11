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
	public function __construct ( $code, $message = '' )
	{
		parent::__construct( $message, $code );
	}
}
