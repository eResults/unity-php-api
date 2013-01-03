<?php

namespace Unity\Exceptions;

class NotFound extends UnityException
{
    protected $message = 'Not found';
	
	protected $code = 404;
}