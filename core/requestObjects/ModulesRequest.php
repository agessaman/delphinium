<?php  namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;

class ModulesRequest extends RootsRequest
{
    
    function __construct() {
        $this->action = ActionType::GET;
    }
}