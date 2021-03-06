<?php

/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */
namespace Delphinium\Irisnew\Components;

use Delphinium\Irisnew\Controllers\IrisChart as MyController;
use Delphinium\Irisnew\Models\IrisChart as MyModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Enums\ActionType;
use Cms\Classes\ComponentBase;
class IrisChart extends ComponentBase
{
    /**
     * @return array An array of details to be shown in the CMS section of OctoberCMS
     */
    public function componentDetails()
    {
        return [
            'name' => 'IrisChart Component', 
            'description' => 'This chart displays a course\'s modules and the student\'s progress in them'
        ];
    }
    /**
     * @return array Array of properties that can be configured in this instance of this component
     */
    public function defineProperties()
    {
        return [
            'instance' => [
                'title' => '(Optional) IrisChart instance', 'description' => 'Select the irischart instance to display. If an instance is selected, it will be the configuration for all courses that use this page. Leaving this field blank will allow different configurations for every course.', 
                'type' => 'dropdown', 
                'default' => 0
            ],
            'filter' => [
                'title'   => 'Filter',
                'description' => 'Display only this module and its children in Iris',
                'placeholder' => 'Select a parent node',
                'type'    => 'dropdown'
            ]
        ];
    }
    /**
     * @return array An array of instances (eloquent models) to populate the instance dropdown to configure this component
     */
    public function getInstanceOptions()
    {
        //In order for this to work, you must create a model and controller to store the instances of this component.
        //Modify use statement above to include the model and controller and uncomment the following code
        $instances = MyModel::all();
        if (count($instances) === 0) {
            return $array_dropdown = ["0" => "No instances available."];
        } else {
            $array_dropdown = ["0" => "- select MyModel Instance - "];
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
                //assuming that the model has id and name fields
            }
            return $array_dropdown;
        }
    }
    /**
     * This function will run every time this component runs. To use this component, drop it on a OctoberCMS page along with the dev component
     * (for development) or LTIConfiguration component (for production)
     */
    public function onRun()
    {
        try {
            //NOTES:
            //Components have database instances. The logic for how they are created is as follows:
            //is an instance set in this component's properties? yes show it
            //else get all instances
            //    is there an instance with this alias_course? yes use it
            //else create dynamicInstance, save new instance, show it
            //************NOTE:**********
            //Requires minimal.htm layout
            //Requires the Dev component set up from Here:
            //https://github.com/ProjectDelphinium/delphinium/wiki/3.-Setting-up-a-Project-Delphinium-Dev-environment-on-localhost
            //**************************
            $config = $this->getInstance();
            //use the record in the component and frontend form
            $this->page['config'] = json_encode($config);
            //Use the primary key of the record you want to update
            $this->page['recordId'] = $config->id;
            if (!isset($_SESSION)) {
                session_start();
            }
            //get LMS roles --used to determine functions and display options
            $roleStr = $_SESSION['roles'];
            $courseId = $_SESSION['courseID'];
            $this->page['role'] = $roleStr;
            $this->page['courseId'] = $courseId;
            $this->page['userId'] = $_SESSION['userID'];

            //Filter by parent node if it has been configured
            $defaultNode = 1;
            $filter = $this->property('filter',$defaultNode);
            $this->page['filter'] = $filter;
            $finalData=array();

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
                        return intval($e['module_id']) === intval($filter);
                    }
                );

                $obj = array_shift($filterObj);
                $finalData = $this->buildTree($modArr,$obj['parent_id'], $filter);

            }
            $this->page['graphData'] = json_encode($finalData);
            //THIS NEXT SECTION WILL PROVIDE TEACHERS WITH FRONT-EDITING CAPABILITIES OF THE BACKEND INSTANCES.
            //A CONTROLLER AND MODEL MUST EXIST FOR THE INSTANCES OF THIS COMPONENT SO THE BACKEND FORM CAN BE USED IN THE FRONT END FOR THE TEACHERS TO USE
            //ALSO, AN INSTRUCTIONS PAGE WITH THE NAME instructor.htm MUST BE ADDED TO YOUR CONTROLLER DIRECTORY, AFTER THE CONTROLLER IS CREATED
            //IN Delphinium\Irisnew\controllers\IrisChart\_instructions.htm
            // include the backend form with instructions for instructor.htm
            if (stristr($roleStr, 'Instructor') || stristr($roleStr, 'TeachingAssistant')) {
                //INCLUDE JS AND CSS
                //include your css. Note: bootstrap.min.css is part of minimal layout.
                //See #10 in https://github.com/ProjectDelphinium/delphinium/wiki/1.-Installation#setup
                //if you desire to use OctoberCMS' ui library (See https://octobercms.com/docs/ui/form) uncomment the following three lines
                $this->addCss('/modules/system/assets/ui/storm.css', 'core');
                $this->addJs('/modules/system/assets/ui/js/flashmessage.js', 'core');
                $this->addCss('/modules/system/assets/ui/storm.less', 'core');
                $this->addCss("/plugins/delphinium/irisnew/assets/css/main.css");
                $this->addJs("/plugins/delphinium/irisnew/assets/js/d3.v3.min.js");
                $this->addJs('/plugins/delphinium/irisnew/assets/js/irischart_instructor.js');

                $formController = new MyController();
                $formController->create('frontend');
                //Append the formController to the page
                $this->page['irischartform'] = $formController;
                //Append the Instructions to the page
                $instructions = $formController->makePartial('irischartinstructions');
                $this->page['irischartinstructions'] = $instructions;
            } else {
                if (stristr($roleStr, 'Learner')) {
                    //code specific to the student.htm goes here
                }
            }
            //Error handling requires nonlti.htm. See #11 in https://github.com/ProjectDelphinium/delphinium/wiki/1.-Installation#setup
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return;
        } catch (Delphinium\Roots\Exceptions\NonLtiException $e) {
            if ($e->getCode() == 584) {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Invalid LMS') {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
            return \Response::make($this->controller->run('error'), 500);
        }
    }
    /**
     * Retrieves instance of this component. If no specific instance was selected in the CMS configuration of this component
     * then it will create a dynamic instance based on the alias_courseId in which this component was launched
     * @param null $name The name of the component
     * @return mixed Instance of Component
     */
    private function getInstance($name = null)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        //if instance has been set
        if ($this->property('instance')) {
            //use the instance set in CMS dropdown
            $config = MyModel::find($this->property('instance'));
        } else {
            if (is_null($name)) {
                $name = $this->alias . '_' . $courseId;
            }
            $config = MyModel::firstOrNew(array('name' => $name));
            if (is_null($config->name)) {
                $config->name = $name;
            }
            if (is_null($config->animate)) {
                $config->animate = 1;
            }
            if (is_null($config->size)) {
                $config->size = 100;
            }
            //TODO: finish setting some default values
        }
        $config->save();
        return $config;
    }
    /**
     * Ajax Handler for when teachers update the component from their view
     * @return string Json encoded instance of component
     */
    public function onUpdate()
    {
        $data = post('IrisChart');
        //model name
        $id = $this->page['irischartrecordId'];
        // convert string to integer
        $config = $this->getInstance($data['name']);
        // retrieve existing record
        //update record with new data coming from POST
        $config->name = $data['name'];
        $config->animate = intval($data['animate']);
        $config->size = intval($data['size']);
        //TODO: must finish updating the rest of the fields in your table
        $config->save();
        // update original record
        return json_encode($config);
        // back to instructor view
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