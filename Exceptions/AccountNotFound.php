<?php

namespace Unity\Exceptions;

class AccountNotFound extends UnityException
{
    protected $message = 'Account doesn\'t exists or can\'t be found';
	
	protected $logPriority = 5;
}