<?php  namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\Models\ModuleItem;

class ModulesRequest extends RootsRequest
{
    public $moduleId;
    public $moduleItemId;
    public $includeContentItems;
    public $includeContentDetails;
    public $params;
    private $module;
    private $moduleItem;
    
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

        
    function __construct($actionType, $moduleId = null, $contentId = null,  
    $includeContentItems = false, $includeContentDetails = false, $params=null, Module $module = null, $moduleItem = null) 
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

        $this->moduleId = $moduleId;
        $this->moduleItemId = $contentId;
        $this->includeContentDetails = $includeContentDetails;
        $this->includeContentItems= $includeContentItems;
        $this->params = $params;
        $this->module = $module;
        $this->moduleItem = $moduleItem;
    }
}