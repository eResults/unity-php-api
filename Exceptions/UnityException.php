<?php

namespace Unity\Exceptions;

class UnityException extends \Exception
{
	protected $logPriority;

	public function getPriority()
	{
		if ( !isset( $this->logPriority) )
		{
			return 1; //\Zend_Log::ALERT
		}
		return $this->logPriority;
	}
	
}