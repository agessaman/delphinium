<?php namespace Delphinium\Roots\Exceptions;

class NonLtiException extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message) {
        // make sure everything is assigned properly
        parent::__construct($message, 584);//584 spells LTI in a phone keyboard.
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}
