<?php

namespace Fatryst\NuoNuo\Exception;

class NuoNuoException extends \Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
