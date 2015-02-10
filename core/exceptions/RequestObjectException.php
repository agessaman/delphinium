<?php namespace Delphinium\Core\Exceptions;

class RequestObjectException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, $requestObject) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
    
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}
