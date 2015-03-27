<?php  namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;

class ModulesRequest extends RootsRequest
{
    public $moduleId;
    public $moduleItemId;
    public $includeContentItems;
    public $includeContentDetails;
    public $params;
    public $module;
    public $moduleItem;
    public $freshData;
    
    function getModule() {
        return $this->module;
    }

    function getModuleItem() {
        return $this->moduleItem;
    }

    function setModule(Module $module) {
        $this->module = $module;
    }

    function setModuleItem($moduleItem) {
        $this->moduleItem = $moduleItem;
    }
    function getFreshData() {
        return $this->freshData;
    }

            
    function __construct($actionType, $moduleId = null, $moduleItemId = null,  
    $includeContentItems = false, $includeContentDetails = false, $params=null, Module $module = null, ModuleItem $moduleItem = null, $freshData = null) 
    {
        if(ActionType::isValidValue($actionType))
        {  
            $this->actionType = $actionType;
        }
        else
        {
            throw new \Exception("Invalid ActionType"); 
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

        $this->moduleId = $moduleId;
        $this->moduleItemId = $moduleItemId;
        $this->includeContentDetails = $includeContentDetails;
        $this->includeContentItems= $includeContentItems;
        $this->params = $params;
        $this->module = $module;
        $this->moduleItem = $moduleItem;
        $this->freshData = $freshData;
    }
}