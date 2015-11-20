<?php namespace Delphinium\Roots\Exceptions;

class InvalidRequestException extends \Exception
{
    public $action;
    public $reason;
    
    function getAction() {
        return $this->action;
    }

    function getReason() {
        return $this->reason;
    }

    function setAction($action) {
        $this->action = $action;
    }

    function setReason($reason) {
        $this->reason = $reason;
    }

    public function __construct($action, $reason, $code=null) {
        $this->setAction($action);
        $this->setReason($reason);
        
        if(is_null($code))
        {
            $code = 1;// see code guide below
        }
        $fullMsg = "An error occurred when attempting to {$action}. Reason: {$reason}";
        
        parent::__construct($fullMsg, $code);
    }
    
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
   
}
