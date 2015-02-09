<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;

class AssignmentsRequest extends RootsRequest
{
    function __construct() {
        $this->action = ActionType::GET;
    }
}