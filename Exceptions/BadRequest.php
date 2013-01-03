<?php

namespace Unity\Exceptions;

class BadRequest extends UnityException
{
    protected $message = 'Bad request';
	
	protected $code = 400;
}