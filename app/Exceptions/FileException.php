<?php

namespace DirectusTools\Exceptions;

use Exception;

class FileException extends Exception
{
    public function errorMessage()
    {
        $errorMsg = "Error on line {$this->getLine()} in {$this->getFile()}: Could not create <b>{$this->getMessage()}</b>";
        return $errorMsg;
    }
}
