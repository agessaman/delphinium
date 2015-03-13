<?php namespace Delphinium\Core\Exceptions;

class InvalidActionException extends \Exception
{
    public $action;
    public $requestObject;
    
    function getRequestObject() {
        return $this->requestObject;
    }

    function setRequestObject($requestObject) {
        $this->requestObject = $requestObject;
    }

    function getAction() {
        return $this->action;
    }

    function setAction($action) {
        $this->action = $action;
    }

    public function __construct($action,$requestObj, $message=null) {
        
        
        $fullMsg = "Action {$action} in {$requestObj} is not allowed";
        if($message)
        {
            $fullMsg.= ": {$message}";
        }
        parent::__construct($fullMsg, null, null);
    }
    
    public function __toString() {
        return __CLASS__ . ": {$this->message}\n";
    }

}
