<?php

namespace DirectusTools\Exceptions;

use Exception;

class RunException extends Exception
{
    public function errorMessage()
    {
        $errorMsg = "Error on line {$this->getLine()} in {$this->getFile()}: <b>{$this->getMessage()}</b>";
        return $errorMsg;
    }
}
