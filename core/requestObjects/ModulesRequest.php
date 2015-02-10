<?php  namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;

class ModulesRequest extends RootsRequest
{
    public $moduleId;
    public $contentId;
    public $includeContentItems;
    public $includeContentDetails;
    
    function __construct($actionType = ActionType::GET, $moduleId = null, $contentId = null, $lms = Lms::Canvas, 
            $includeContentItems = false, $includeContentDetails = false) 
    {
    }
}