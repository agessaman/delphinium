<?php namespace Delphinium\Stem\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Stem\Classes\ManagerHelper;
use Delphinium\Roots\Enums\Lms;

class Manager extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Stem Manager',
            'description' => 'Module Manager'
        ];
    }

    public function onRun()
    {
        //try
        //{
            $this->addJs("/plugins/delphinium/stem/assets/javascript/angular.min.js");
            $this->addJs("/plugins/delphinium/stem/assets/javascript/angular-ui-tree.js");
            //        $this->addJs("/plugins/delphinium/stem/assets/javascript/bodyCtrl.js");
            //        $this->addJs("/plugins/delphinium/stem/assets/javascript/alertService.js");
            $this->addJs("/plugins/delphinium/stem/assets/javascript/tree.js");
            $this->addJs('/plugins/delphinium/stem/assets/javascript/xeditable.min.js');
            $this->addJs('/plugins/delphinium/stem/assets/javascript/ui-bootstrap-tpls-0.12.1.min.js');
            $this->addJs("/plugins/delphinium/stem/assets/javascript/jobid.modal.controller.js");
            $this->addJs("/plugins/delphinium/stem/assets/javascript/itemModal.controller.js");
            $this->addJs("/plugins/delphinium/stem/assets/javascript/addItemController.js");
            $this->addJs("/plugins/delphinium/stem/assets/javascript/moduleModal.js");

            $this->addCss('/plugins/delphinium/stem/assets/css/module-tree.css');
            $this->addCss('/plugins/delphinium/stem/assets/css/bootstrap.min.css');
            $this->addCss('/plugins/delphinium/stem/assets/css/xeditable.css');
            $this->addCss('/plugins/delphinium/stem/assets/css/angular-ui-tree.min.css');
            $this->addCss('/plugins/delphinium/stem/assets/css/font-awesome.css');

            if(!isset($_SESSION))
            {
                session_start();
            }
            $this->page['courseId'] = $_SESSION['courseID'];
            $this->page['lmsUrl'] =  json_encode($this->getLmsUrl());
            $this->prepareData(false);

        // }catch (\GuzzleHttp\Exception\ClientException $e) {
        //     return;
        // }
        // catch(Delphinium\Roots\Exceptions\NonLtiException $e)
        // {
        //     if($e->getCode()==584)
        //     {
        //         return \Response::make($this->controller->run('nonlti'), 500);
        //     }
        // }
        // catch(\Exception $e)
        // {
        //     if($e->getMessage()=='Invalid LMS')
        //     {
        //         return \Response::make($this->controller->run('nonlti'), 500);
        //     }
        //     return \Response::make($this->controller->run('error'), 500);
        // }
    }


    public function prepareData($freshData)
    {
        $roots = new Roots();
        $tempArray = $this->getModules($freshData);

        $this->page['moduleData'] = json_encode($tempArray);
        $tags = $roots->getAvailableTags();
        if(strlen($tags)>0)
        {
            $tags = explode(', ', $tags);
        }
        else
        {
            $tags = [];
        }
        $this->page['avTags'] = json_encode($tags);

        $completionReqs = $roots->getCompletionRequirementTypes();
        $result = array();
        $i=0;
        foreach($completionReqs as $type)
        {
            $item = new \stdClass();

            $item->id = $i;
            $item->value=$type;
            $item->text = $this->getText($type);
            $result[] = $item;

            $i++;
        }
        $this->page['completionRequirementTypes']= json_encode($result);
    }
    public function getModules($freshData)
    {
        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = true;
        $includeContentItems = true;
        $module = null;
        $moduleItem = null;

        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems,
            $includeContentDetails, $module, $moduleItem , $freshData);

        $roots = new Roots();
        $moduleData = $roots->modules($req);
        $modArr = $moduleData->toArray();

        $simpleModules = array();
        foreach($modArr as $item)
        {
            $mod = new \stdClass();

            $mod->id = $item['module_id'];
            $mod->value=$item['name'];
            $simpleModules[] = $mod;
        }
        $this->page['rawData'] = json_encode($simpleModules);

        $roots = new Roots();
        $result = $roots->buildTree($modArr);

        $tempArray =array();

        if(count($result)<1) //there weren't any parent-child relationships
        {
            $parent;
            $allChildren;
            $final = array();

            //The parent will be the first PUBLISHED item
            $firstItem = '';
            foreach($moduleData as $item)
            {
                if($item['published'] == "1")
                {
                    $firstItem = $item;
                    break;
                }
            }

            $newArr = $this->unsetValue($modArr, $firstItem);//remove parent from array
            $firstParentId=$firstItem["module_id"];
            $i=0;
            foreach($newArr as $item)
            {
                $item["parent_id"] = $firstParentId;
                //each item must have a parentId of the first module
                $item["children"] = [];
                $item["order"] = $i;
                $final[] = $item;
                $i++;
            }

            //remove the first Item (which is the parent)
            $firstItem["parent_id"] = 1;
            $firstItem["children"]=$final;
            $firstItem["order"]=0;

            $tempArray[] = $firstItem;
        }
        else
        {
            $tempArray = $result;
        }
        return $tempArray;
    }


    private function unsetValue(array $array, $value, $strict = TRUE)
    {
        if(($key = array_search($value, $array, $strict)) !== FALSE) {
            unset($array[$key]);
        }
        return $array;
    }

    private function getLmsUrl()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            switch ($lms)
            {
                case (Lms::CANVAS):
                    $domain = $_SESSION['domain'];
                    $courseId = $_SESSION['courseID'];
                    $url= "https://{$domain}/courses/{$courseId}/";
                    return $url;
                default:
                    $domain = $_SESSION['domain'];
                    $courseId = $_SESSION['courseID'];
                    $url= "https://{$domain}/courses/{$courseId}/";
                    return $url;
            }
        }

    }

    private function getText($type)
    {
        switch($type)
        {
            case 'must_view':
                return "view the item";
            case 'must_contribute':
                return 'contribute';
            case 'must_submit':
                return "score at least";
        }
    }
}