<?php

namespace Unity\Exceptions;

class DNSAlreadyExists extends UnityException
{
    protected $message = 'DNS entry already exists in DNS cluster';
	
}