<?php

namespace Unity\Exceptions;

class SessionExpired extends UnityException
{
    protected $message = 'Session expired';
	
	protected $code = 420;
	
	protected $logPriority = 5;
}