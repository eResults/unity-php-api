<?php

namespace Unity\Exceptions;

class Forbidden extends UnityException
{
    protected $message = 'Forbidden';
	
	protected $code = 403;
}