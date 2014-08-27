<?php

namespace Unity\Exceptions;

class UnityException extends \Exception
{
	protected $logPriority;

	public function getPriority()
	{
		if ( !isset( $this->logPriority) )
		{
			return 1;
		}
		return $this->logPriority;
	}
	
	/**
	 * Optional extra data
	 * @var mixed 
	 */
	protected $data;
	
	public function getData()
	{
		return $this->data;
	}

	public function setData( $data )
	{
		$this->data = $data;
		return $this;
	}

}