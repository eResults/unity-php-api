<?php

namespace Unity\Exceptions;

class Conflict extends UnityException
{
    protected $message = 'Conflict';
	
	protected $code = 409;
}