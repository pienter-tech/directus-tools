<?php

namespace DirectusTools\Exceptions;


use Exception;

class ArgumentNotFoundException extends Exception
{
    public function errorMessage() {
        $errorMsg = "Error on line {$this->getLine()} in {$this->getFile()}: <b>{$this->getMessage()}</b> argument not found";
        return $errorMsg;
    }
}
