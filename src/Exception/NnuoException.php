<?php
/**
 * 诺诺开放平台异常类.
 *
 * @author liqiao
 */

namespace Fatryst\NuoNuo\Exception;

class NuoNuoException extends \Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
