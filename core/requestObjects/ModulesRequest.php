<?php  namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;

class ModulesRequest extends RootsRequest
{
    public $moduleId;
    public $moduleItemId;
    public $includeContentItems;
    public $includeContentDetails;
    public $params;
    
    function getModuleId() {
        return $this->moduleId;
    }

    function getModuleItemId() {
        return $this->moduleItemId;
    }

    function getIncludeContentItems() {
        return $this->includeContentItems;
    }

    function getIncludeContentDetails() {
        return $this->includeContentDetails;
    }

    function getParams() {
        return $this->params;
    }

    function setModuleId($moduleId) {
        $this->moduleId = $moduleId;
    }

    function setModuleItemId($moduleItemId) {
        $this->moduleItemId = $moduleItemId;
    }

    function setIncludeContentItems($includeContentItems) {
        $this->includeContentItems = $includeContentItems;
    }

    function setIncludeContentDetails($includeContentDetails) {
        $this->includeContentDetails = $includeContentDetails;
    }

    function setParams($params) {
        $this->params = $params;
    }

    
    function __construct($actionType, $moduleId = null, $contentId = null,  
    $includeContentItems = false, $includeContentDetails = false, $params=null) 
    {
        if(ActionType::isValidValue($actionType))
        {  
            $this->actionType = $actionType;
        }

        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $this->lms = $lms;   
        }
        else
        {
            throw new \Exception("Invalid LMS"); 
        }

        $this->setModuleItemId($contentId);
        $this->setIncludeContentDetails($includeContentDetails);
        $this->setIncludeContentItems($includeContentItems);
        $this->setModuleId($moduleId);
        $this->setParams($params);
    }
}