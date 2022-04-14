<?php

namespace Krognome\Approvalstudio\Facades;

use Illuminate\Support\Facades\Facade;

class Approvalstudio extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'approvalstudio';
    }
}
