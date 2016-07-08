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

namespace Delphinium\Roots\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;
use Delphinium\Roots\RequestObjects\AssignmentsRequest;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\CompletionRequirementType;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Models\Page;
use \DateTime;
use \DateTimeZone;

class RestfulApi extends Controller 
{
    public function getContentByType()
    {
        $roots = new Roots();
        $type = \Input::get('type');
        
        switch($type)
        {
            case "File":
                $response = $roots->getFiles();
                break;
            case "Page":
                $response = $roots->getPages();
                break;
            case "Assignment":
                $req = new AssignmentsRequest(ActionType::GET, null, false);
                $response = $roots->assignments($req);

                $return =array();
                $i=0;
                $assignments = array();
                foreach($response as $item)
                {
                    $file = new \stdClass();

                    $file->id = $item['assignment_id'];
                    $file->name=$item['name'];
                    $assignments[] = $file;

                    $i++;
                }
                return $assignments;
                
                
            case "Quiz":
                $response = $roots->getQuizzes();
                break;
            case "ExternalTool":
                $response = $roots->getExternalTools();
                break;
            default:
                $response = [];
        }
        
        return json_encode($response);
    }
    
    public function addModule()
    {
        $name = \Input::get('name');
        $date =\Input::get('unlock_at');
        if(!is_null($date))
        {//convert to UTC from current timezone
            $unlock_at = $this->getUTCdate($date);
        }
        else
        {
            $unlock_at = null;
        }
        $prerequisite_module_ids =\Input::get('prerequisites');
        $published = \Input::get('published');
        
        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, null);
        
        $req = new ModulesRequest(ActionType::POST, null, null,  
            false, false, $module, null , false);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        
//        //add the parent_id;
        $parent_id = \Input::get('parent_id');
        $res->parent_id = $parent_id;
        $roots->updateModuleParent($res);
        return json_encode($res);
    }
    
    public function addModuleItem()
    {
        $name = \Input::get('name');
        $contentId = \Input::get('id');
        $moduleId = \Input::get('module_id');
        $type = \Input::get('type');
        $url = \Input::get('url');
        $page_url = null;
        $external_url = null;
        switch($type)
        {
            case "Page":
                $page_url = ($url);
                break;
            case "ExternalUrl":
                $external_url = ($url);
                break;
            case "ExternalTool":
                $external_url = ($url);
                break;
        }
        
        //$title = null, $module_item_type=null, $content_id = null, $page_url = null, $external_url = null, 
//        $completion_requirement_type = null, $completion_requirement_min_score = null, $published = false, $position = 1,array $tags = null)
        //TODO: look into completion requirement type
        $moduleItem = new ModuleItem($name, $type, $contentId, $page_url,$external_url, null, 
                null, true, null, null);
                
        $req = new ModulesRequest(ActionType::POST, $moduleId, null,  
            false, false,  null, $moduleItem , false);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        return json_encode($res);
    }
    
    
    public function updateModule()
    {
        $name = \Input::get('name');
        $date = \Input::get('unlock_at');
        $unlock_at = new DateTime($date);
        $prerequisite_module_ids =\Input::get('prerequisites');
        $published = \Input::get('published');
        $module_id = \Input::get('module_id');
        
        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, null);
        
        //update a module (changing title and published to false)
        $req = new ModulesRequest(ActionType::PUT, $module_id, null,  
            false, false, $module, null , false);
        
        $roots = new Roots();
        return $roots->modules($req);
    }
    
    public function updateModuleItem()
    {
//        $name = \Input::get('name');
//        $date = \Input::get('unlock_at');
//        $unlock_at = new DateTime($date);
//        $prerequisite_module_ids =\Input::get('prerequisites');
//        $published = \Input::get('published');
//        $module_id = \Input::get('module_id');
//        
//        
//         $tags = null;//array('New Tag', 'Another New Tag');
//        $title = "New Title from back end";
//        $modItemType = null;// Module type CANNOT be updated
//        $content_id = 2078183;
//        $completion_requirement_min_score = null;//7;
//        $completion_requirement_type = null;//CompletionRequirementType::MUST_SUBMIT;
//        $page_url = null;//"http://www.gmail.com";
//        $published = true;
//        $position = 1;//2;
//        
//        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, null, $completion_requirement_type, 
//                $completion_requirement_min_score, $published, $position, $tags);
//        //end added
//        
//        $moduleId = 457097;
//        $moduleItemId = 2885671;
//        $includeContentItems = false;
//        $includeContentDetails = false;
//        $module = null;
//        $freshData = false;
//        
//        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
//            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
//        
//        $roots = new Roots();
//        $res = $roots->modules($req);
    
    }
    
    /**
     * 
     * @param type $localTimezoneStringDate String representation of the date in local timezone.
     * @return type The DateTime in UTC timezone
     */
    private function getUTCdate($localTimezoneStringDate)
    {//we are comingin with MST and need to convert to UTC
        if (!isset($_SESSION)) {
            session_start();
        }
        $timezoneStr = $_SESSION['timezone']->timezone;
        $date = new DateTime($localTimezoneStringDate, new DateTimeZone($timezoneStr));
        $UTC = new DateTimeZone("UTC");
        $unlock_at = $date->setTimezone($UTC);
        return $unlock_at;
    }
    
}
