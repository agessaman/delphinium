<?php namespace Delphinium\Blossom\Components;

use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Models\ModuleItem as DbModuleItem;
use Delphinium\Roots\Roots;
use Delphinium\Roots\RequestObjects\SubmissionsRequest;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\RequestObjects\AssignmentsRequest;
use Delphinium\Roots\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Roots\Enums\CommonEnums\ActionType;
use Delphinium\Roots\Enums\ModuleItemEnums\ModuleItemType;
use Delphinium\Roots\Enums\ModuleItemEnums\CompletionRequirementType;
use Cms\Classes\ComponentBase;
use \DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostFile;

use Delphinium\Roots\Guzzle\GuzzleHelper;

class Progress extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Progress',
            'description' => 'Shows students progress toward finishing the course'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/progress.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/progress.css");
        
        $req = new AssignmentsRequest(ActionType::GET);
        
        $roots = new Roots();
        $res = $roots->assignments($req);
        $this->page['assignments'] = json_encode($res);
    }

}