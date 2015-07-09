<?php  namespace Delphinium\Roots\RequestObjects;

use Delphinium\Roots\Enums\CommonEnums\Lms;
use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;

class ModulesRequest extends RootsRequest
{
    private $moduleId;
    private $moduleItemId;
    private $includeContentItems;
    private $includeContentDetails;
    public $module;
    public $moduleItem;
    private $freshData;
   
    function getModuleId() {
        return $this->moduleId;
    }

    function getModuleItemId() {
        return $this->moduleItemId;
    }

    function getIncludeContentItems() {
        return $this->includeContentItems;
    }
    
    function setIncludeContentItems($include) {
        $this->includeContentItems = $include;
    }

    function getIncludeContentDetails() {
        return $this->includeContentDetails;
    }
    
    function setIncludeContentDetails($include) {
        $this->includeContentDetails = $include;
    }

    function getModule() {
        return $this->module;
    }

    function getModuleItem() {
        return $this->moduleItem;
    }

    function getFreshData() {
        return $this->freshData;
    }
    
    function setFreshData($fresh_data) {
        $this->freshData = $fresh_data;
    }

    function setModule(Module $module) {
        $this->module = $module;
    }

    function setModuleItem($moduleItem) {
        $this->moduleItem = $moduleItem;
    }
    
            
    function __construct($actionType, $moduleId = null, $moduleItemId = null,  $includeContentItems = false, 
            $includeContentDetails = false, Module $module = null, ModuleItem $moduleItem = null, $freshData = null) 
    {
        //this takes care of setting the lms and the ActionType in the parent class (RootsRequest)
        parent::__construct($actionType);
        

        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $this->lms = $lms;
        }
        else
        {
            throw new \Exception("Invalid LMS"); 
        }

        $this->moduleId = $moduleId;
        $this->moduleItemId = $moduleItemId;
        $this->includeContentDetails = $includeContentDetails;
        $this->includeContentItems= $includeContentItems;
        $this->module = $module;
        $this->moduleItem = $moduleItem;
        $this->freshData = $freshData;
    }
}