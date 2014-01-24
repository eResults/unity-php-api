<?php

namespace Unity\Exceptions;

class Unauthorized extends UnityException
{
    protected $message = "Unauthorized";
	
	protected $code = 401;
	
	protected $logPriority = 5;
}