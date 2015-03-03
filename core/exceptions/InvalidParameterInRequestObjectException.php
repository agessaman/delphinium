<?php namespace Delphinium\Core\Exceptions;

class InvalidParameterInRequestObjectException extends \Exception
{
    
    function getRequestObject() {
        return $this->requestObject;
    }

    function getParameterName() {
        return $this->parameterName;
    }

    function setRequestObject($requestObject) {
        $this->requestObject = $requestObject;
    }

    function setParameterName($parameterName) {
        $this->parameterName = $parameterName;
    }

    public $requestObject;
    public $parameterName;
    
    public function __construct($requestObject, $parameter, $message=null) {
        
        $code = 1;// see code guide below
        $fullMsg = "Parameter {$parameter} in RequestObject {$requestObject} is not valid";
        if($message)
        {
            $fullMsg.= ": {$message}";
        }
        parent::__construct($fullMsg, $code);
    }
    
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    /*
     * Code guide:
     * 1 = invalid parameter
     */

}
