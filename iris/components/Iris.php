<?php namespace Delphinium\Iris\Components;

use Delphinium\Stem\Models\Home as IrisCharts;
use Delphinium\Roots\Roots;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Enums\ActionType;
use Cms\Classes\ComponentBase;

class Iris extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Iris Chart',
            'description' => 'This chart displays a course\'s modules and the student\'s progress in them'
        ];
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/iris/assets/javascript/jquery.min.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/d3.v3.min.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/iris.js");
        $this->addCss("/plugins/delphinium/iris/assets/css/main.css");

    }
    public function onRender()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }

        $courseId = $_SESSION['courseID'];
        $this->page['courseId'] = $courseId;
        $this->page['userId'] = $_SESSION['userID'];

        //Filter by parent node if it has been configured
        $defaultNode = 1;
        $filter = $this->property('filter',$defaultNode);
        $this->page['filter'] = $filter;
        $finalData;

        $freshData = false;
        $req = new ModulesRequest(ActionType::GET, null, null, true, true, null, null , $freshData);

        $roots = new Roots();
        $moduleData = $roots->modules($req);

        $this->page['rawData'] = json_encode($moduleData);
        $modArr = $moduleData->toArray();

        if($filter===$defaultNode)
        {///get all items
            $finalData = $this->buildTree($modArr,1);
        }
        else
        {//filter by node
            $filterObj = array_filter(
                $modArr,
                function ($e) use ($filter) {
                    return $e['module_id'] === $filter;
                }
            );
            $obj = array_shift($filterObj);
            $finalData = $this->buildTree($modArr,$obj['parent_id'], $filter);
        }
        $this->page['graphData'] = json_encode($finalData);
    }

    public function defineProperties()
    {
        return [
            'filter' => [
                'title'   => 'Filter',
                'description' => 'Display only this module and its children in Iris',
                'placeholder' => 'Select a parent node',
                'type'    => 'dropdown'
            ]
        ];
    }

    public function getFilterOptions()
    {
        $req = new ModulesRequest(ActionType::GET, null, null, true,
            true, null, null , false);
        $roots = new Roots();
        $moduleData = $roots->modules($req);
        $arr = $moduleData->toArray();

        $tree = $this->buildTree($arr, 1);
        $dash = "";
        $result = array();
        $result[$tree[0]['module_id']] = "({$tree[0]['name']})";

        foreach($tree as $item)
        {
            $this->recursion($item['children'], $dash, $result);
        }
        return $result;
    }


    private function recursion($children, &$dash, &$res)
    {
        foreach($children as $item)
        {
            $res[$item['module_id']] = $dash." ".$item['name'];
            if(sizeof($item['children'])>=1)
            {
                $newDash = $dash."-";
                $this->recursion($item['children'], $newDash, $res);
            }
        }
    }

    private function buildTree(array &$elements, $parentId = 1, $moduleFilter=null) {
        $branch = array();
        foreach ($elements as $key=>$module) {
            if($module['published'] == "1")//if not published don't include it
            {
                if(!is_null($moduleFilter)&&($module['module_id']!=$moduleFilter))
                {//if we have a filter and this module doesn't match the filter, skip the item
                    unset($elements[$module['module_id']]);
                    continue;
                }
                if ($module['parent_id'] == $parentId) {
                    $children = $this->buildTree($elements, $module['module_id']);
                    if ($children) {
                        $module['children'] = $children;
                    }
                    else
                    {
                        $module['children'] = array();
                    }
                    $branch[] = $module;
                    unset($elements[$module['module_id']]);
                }
            }
        }

        return $branch;
    }



}