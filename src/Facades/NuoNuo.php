<?php

namespace Fatryst\NuoNuo\Facades;

use Illuminate\Support\Facades\Facade;

class NuoNuo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'nuonuo';
    }
}
