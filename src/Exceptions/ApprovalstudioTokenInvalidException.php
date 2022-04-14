<?php

namespace Krognome\Approvalstudio\Exceptions;

use Exception;

class ApprovalstudioTokenInvalidException extends Exception
{
    public function __construct($message = 'User with given email and password not found. Either the provided credentials are wrong or a user is locked and is no able to login anymore.')
    {
        parent::__construct($message);
    }
}