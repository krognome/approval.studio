<?php

namespace Krognome\Approvalstudio\Exceptions;

use Exception;

class ApprovalstudioRequestFailedException extends Exception
{
    public function __construct($message = 'Approval.studio service requests failed. Please make sure gazette service is reachable.')
    {
        parent::__construct($message);
    }
}