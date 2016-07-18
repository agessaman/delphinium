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

namespace Delphinium\Roots;

use Delphinium\Roots\Models\AssignmentGroup;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Models\Page;
use Delphinium\Roots\Models\File;
use Delphinium\Roots\Models\Quiz;
use Delphinium\Roots\Models\Discussion;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Models\Module as DbModule;
use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\Enums\Lms;
use Delphinium\Roots\Enums\DataType;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Enums\ModuleItemType;
use Delphinium\Roots\Enums\PageEditingRoles;
use Delphinium\Roots\Enums\CompletionRequirementType;
use Delphinium\Roots\Lmsclasses\CanvasHelper;
use Delphinium\Roots\Exceptions\InvalidActionException;
use Delphinium\Roots\Exceptions\InvalidRequestException;
use Delphinium\Roots\DB\DbHelper;

class Roots
{
    public $dbHelper;
    public $canvasHelper;

    function __construct()
    {
        $this->dbHelper = new DbHelper();
        $this->canvasHelper = new CanvasHelper();
    }
    /*
     * Public Functions
     */

    public function modules(ModulesRequest $request)
    {
        switch($request->getActionType())
        {
            case (ActionType::GET):

                if(!$request->getFreshData())
                {
                    $data = $this->dbHelper->getModuleData($request);

                    //depending on the request we can get an eloquent collection or one of our models. Need to validate them differently
                    switch(get_class($data))
                    {
                        case "Illuminate\Database\Eloquent\Collection":
                            return (!$data->isEmpty()) ?  $data :  $this->getModuleDataFromLms($request);
                        default:
                            return (!is_null($data)) ? $data : $this->getModuleDataFromLms($request);
                    }
                }
                else
                {
                    return $this->getModuleDataFromLms($request);
                }
                break;

            case(ActionType::PUT):
                switch ($request->getLms())
                {
                    case (Lms::CANVAS):
                        $canvas = new CanvasHelper(DataType::MODULES);
                        return $canvas->putModuleData($request);
                    default:
                        $canvas = new CanvasHelper(DataType::MODULES);
                        return $canvas->putModuleData($request);
                }
                break;
            case(ActionType::POST):
                switch ($request->getLms())
                {
                    case (Lms::CANVAS):
                        $canvas = new CanvasHelper(DataType::MODULES);
                        return $canvas->postModuleData($request);
                    default:
                        $canvas = new CanvasHelper(DataType::MODULES);
                        return $canvas->postModuleData($request);
                }
                break;
            case(ActionType::DELETE):
                switch ($request->getLms())
                {
                    case (Lms::CANVAS):
                        $canvas = new CanvasHelper(DataType::MODULES);
                        return $canvas->deleteModuleData($request);
                    default:
                        $canvas = new CanvasHelper(DataType::MODULES);
                        return $canvas->deleteModuleData($request);
                }
                break;
        }

    }

    public function submissions(SubmissionsRequest $request, $params =null)
    {
        switch($request->getActionType())
        {
            case(ActionType::GET):
                $result = null;
                switch ($request->getLms())
                {
                    case (Lms::CANVAS):
                        $canvas = new CanvasHelper(DataType::SUBMISSIONS);
                        $result = $canvas->processSubmissionsRequest($request);
                        break;
                    default:
                        $canvas = new CanvasHelper(DataType::SUBMISSIONS);
                        $result = $canvas->processSubmissionsRequest($request);
                        break;
                }
                return $result;
            case(ActionType::POST):
                if(is_null($params))
                {
                    throw new InvalidRequestException("post submission", "no parameters in submission");
                }
                $canvas = new CanvasHelper(DataType::SUBMISSIONS);
                $result = $canvas->postSubmission($request, $params);
                return $result;
            case(ActionType::PUT):
                if(is_null($params))
                {
                    throw new InvalidRequestException("put submission", "no parameters in submission");
                }
                $canvas = new CanvasHelper(DataType::SUBMISSIONS);
                $result = $canvas->putSubmission($request, $params);
                return $result;
            default :
                throw new InvalidActionException($request->getActionType(), get_class($request));

        }
    }

    public function assignments(AssignmentsRequest $request)
    {
        switch($request->getActionType())
        {
            case(ActionType::GET):

                if(!$request->getFresh_data())
                {
                    $data = $this->dbHelper->getAssignmentData( $request);
                    return (count($data)>1) ? $data : $this->getAssignmentDataFromLms($request);
                }
                else
                {
                    return $this->getAssignmentDataFromLms($request);
                }
                break;
            case(ActionType::POST):
                switch ($request->getLms())
                {
                    case (Lms::CANVAS):
                        $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                        return $canvas->addAssignment($request);
                    default:
                        $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                        return $canvas->addAssignment($request);
                }
            case(ActionType::PUT):
                switch ($request->getLms())
                {
                    case (Lms::CANVAS):
                        $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                        return $canvas->updateAssignment($request);
                    default:
                        $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                        return $canvas->updateAssignment($request);
                }
            //If another action was given throw exception
            default :
                throw new InvalidActionException($request->getActionType(), get_class($request));
        }
    }

    public function assignmentGroups(AssignmentGroupsRequest $request, AssignmentGroup $group =null)
    {
        switch($request->getActionType())
        {
            case(ActionType::GET):
                if(!$request->getFresh_data())
                {
                    $data = $this->dbHelper->getAssignmentGroupData($request);
                    switch(get_class($data))
                    {
                        case "Illuminate\Database\Eloquent\Collection":
                            return (!$data->isEmpty()) ?  $data :  $this->getAssignmentGroupDataFromLms($request);
                        default:
                            return (!is_null($data)) ? $data : $this->getAssignmentGroupDataFromLms($request);
                    }
                }
                else
                {
                    return $this->getAssignmentGroupDataFromLms($request);
                }

                break;
            case(ActionType::POST);
                return $this->canvasHelper->postAssignmentGroup($request, $group);
                break;
            default :
                throw new InvalidActionException($request->getActionType(), get_class($request));
        }
    }


    /*
     * OTHER HELPER METHODS
     */

    public function updateModuleOrder($modules, $updateLms)
    {
        $ordered = array();
        $order = 1;//canvas uses 1-based position
        $new=array();
        foreach($modules as $item)
        {
            if($updateLms)
            {
//              UPDATE positioning in LMS
                $module = new Module(null, null, null, null, $order);
                $req = new ModulesRequest(ActionType::PUT, $item->module_id, null,
                    false, false, $module, null , false);
                $res = $this->modules($req);

                $order++;

            }
            //UPDATE positioning in DB
            $orderedModule = $this->dbHelper->updateOrderedModule($item);
            array_push($ordered, $orderedModule->toArray());
        }
        return $ordered;
    }

    public function updateModuleParent(DbModule $module)
    {
        $this->dbHelper->updateOrderedModule($module);
    }
    public function addPage(Page $page)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        switch ($lms)
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper();
                return $canvas->addPage($page);
                break;
            default:
                $canvas = new CanvasHelper();
                return $canvas->addPage($page);
                break;
        }
    }

    public function addDiscussion(Discussion $discussion)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        switch ($lms)
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper();
                return $canvas->addDiscussion($discussion);
            default:
                $canvas = new CanvasHelper();
                return $canvas->addDiscussion($discussion);
        }
    }

    public function addQuiz(Quiz $quiz)
    {

        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        switch ($lms)
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper();
                return $canvas->addQuiz($quiz);
            default:
                $canvas = new CanvasHelper();
                return $canvas->addQuiz($quiz);
        }
    }

    public function addExternalTool($externalTool)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        switch ($lms)
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper();
                return $canvas->addExternalTool($externalTool);
            default:
                $canvas = new CanvasHelper();
                return $canvas->addExternalTool($externalTool);
        }
    }

    public function uploadFile(File $file)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        switch ($lms)
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper();
                return $canvas->uploadFile($file);
            default:
                $canvas = new CanvasHelper();
                return $canvas->uploadFile($file);
        }
    }

    public function uploadFileStepTwo($params, $file, $upload_url)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        switch ($lms)
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper();
                return $canvas->uploadFileStepTwo($params, $file, $upload_url);
            default:
                $canvas = new CanvasHelper();
                return $canvas->uploadFileStepTwo($params, $file, $upload_url);
        }

    }

    public function uploadFileStepThree($location)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        switch ($lms)
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper();
                return $canvas->uploadFileStepThree($location);
            default:
                $canvas = new CanvasHelper();
                return $canvas->uploadFileStepThree($location);
        }

    }

    public function getAvailableTags()
    {
        return $this->dbHelper->getAvailableTags();
    }

    public function getModuleStates(ModulesRequest $request)
    {
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvasHelper = new CanvasHelper();
                return $canvasHelper->getModuleStates($request);
            default:
                $canvasHelper = new CanvasHelper();
                return $canvasHelper->getModuleStates($request);
        }
    }

    public function getModuleTree($freshData)
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

        $result = $this->buildTree($modArr);
        return $result;
    }

    public function buildTree(&$elements, $parentId = 1) {
        $branch = array();
        $order = 0;
        $newItems= array();
        foreach ($elements as $module) {
            if ($module['parent_id'] == $parentId)
            {
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
        return $branch;

    }

    public function getModuleItemTypes()
    {
        return ModuleItemType::getConstants();
    }

    public function getCompletionRequirementTypes()
    {
        return CompletionRequirementType::getConstants();
    }
    public function getPageEditingRoles()
    {
        return PageEditingRoles::getConstants();
    }

    public function getFiles()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $files = array();
            switch ($lms)
            {
                case (Lms::CANVAS):
                    $canvasHelper = new CanvasHelper();
                    $files =($canvasHelper->getFiles());
                    break;
                default:
                    $canvasHelper = new CanvasHelper();
                    $files = ($canvasHelper->getFiles());
                    break;
            }

            $return =array();
            $i=0;
            foreach($files as $item)
            {
                $file = new \stdClass();

                $file->id = $item->id;
                $file->name=$item->display_name;
                $return[] = $file;

                $i++;
            }
            return $return;
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getPages()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $pages = array();
            switch ($lms)
            {
                case (Lms::CANVAS):
                    $canvasHelper = new CanvasHelper();
                    $pages = ($canvasHelper->getPages());
                    break;
                default:
                    $canvasHelper = new CanvasHelper();
                    $pages = $canvasHelper->getPages();
                    break;
            }

            $return =array();
            $i=0;
            foreach($pages as $item)
            {
                $file = new \stdClass();

                $file->id = $item->page_id;
                $file->name=$item->title;
                $file->url = $item->url;
                $return[] = $file;

                $i++;
            }
            return $return;
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function quizzes(QuizRequest $request)
    {
        switch($request->getActionType())
        {
            case (ActionType::GET):

                if(!$request->getFresh_data())
                {
                    $data = $this->dbHelper->getQuizzes($request);

                    //depending on the request we can get an eloquent collection or one of our models. Need to validate them differently
                    switch(get_class($data))
                    {
                        case "Illuminate\Database\Eloquent\Collection":
                            if($data->isEmpty()||($request->getInclude_questions()&& count($data->first()->questions)<1))
                            {
                                return $this->getQuizzesFromLms($request);
                            }
                            else
                            {
                                return $data;
                            }
//                            return (!$data->isEmpty()) ?  $data :  $this->getQuizzesFromLms($request);
                        default:
                            if(is_null($data)||($request->getInclude_questions()&&count($data->questions)<1))
                            {
                                return $this->getQuizzesFromLms($request);
                            }
                            else
                            {
                                return $data;
                            }

                    }
                }
                else
                {
                    return $this->getQuizzesFromLms($request);
                }
                break;
        }


    }
    public function getExternalTools()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $tools = array();
            switch ($lms)
            {
                case (Lms::CANVAS):
                    $canvasHelper = new CanvasHelper();
                    $tools =  ($canvasHelper->getExternalTools());
                    break;
                default:
                    $canvasHelper = new CanvasHelper();
                    $tools = ($canvasHelper->getExternalTools());
                    break;
            }

            $return =array();
            $i=0;
            foreach($tools as $item)
            {
                $file = new \stdClass();

                $file->id = $item->id;
                $file->name=$item->name;
                $file->url = $item->url;
                $return[] = $file;

                $i++;
            }
            return $return;
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getAnalyticsAssignmentData($includeTags = false)
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
                    $canvasHelper = new CanvasHelper();
                    $data = ($canvasHelper->getAnalyticsAssignmentData());
                    if($includeTags)
                    {
                        $result = [];
                        foreach($data as $item)
                        {
                            $item->tags = $canvasHelper->matchAssignmentIdWithTags($item->assignment_id);
                            $result[] = $item;
                        }

                        return $result;
                    }
                    return $data;
                default:
                    $canvasHelper = new CanvasHelper();
                    $data = ($canvasHelper->getAnalyticsAssignmentData());
                    if($includeTags)
                    {
                        $result = [];
                        foreach($data as $item)
                        {
                            $item->tags = $canvasHelper->matchAssignmentIdWithTags($item->assignment_id);
                            $result[] = item;
                        }

                        return $result;
                    }
                    return $data;
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }
    public function getAnalyticsStudentAssignmentData($includeTags = false, $userId = null)
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
                    $canvasHelper = new CanvasHelper();
                    $data = ($canvasHelper->getAnalyticsStudentAssignmentData($userId));
                    if($includeTags)
                    {
                        $result = [];
                        foreach($data as $item)
                        {
                            $item->tags = $canvasHelper->matchAssignmentIdWithTags($item->assignment_id);
                            $result[] = $item;
                        }

                        return $result;
                    }
                    return $data;
                default:
                    $canvasHelper = new CanvasHelper();
                    $data = ($canvasHelper->getAnalyticsStudentAssignmentData($userId));
                    if($includeTags)
                    {
                        $result = [];
                        foreach($data as $item)
                        {
                            $item->tags = $canvasHelper->matchAssignmentIdWithTags($item->assignment_id);
                            $result[] = item;
                        }

                        return $result;
                    }
                    return $data;
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getUsersInCourse()
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
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getUsersInCourse());
                default:
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getUsersInCourse());
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getStudentsInCourse()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        $courseId = $_SESSION['courseID'];
        $users = $this->dbHelper->getUsersInCourseWithRole($courseId, 'Learner');
        if(count($users)>1)
        {
//            return $users;
        }

        //if no users were found in DB try to get them from Canvas
        if(Lms::isValidValue($lms))
        {
            switch ($lms)
            {
                case (Lms::CANVAS):
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getStudentsInCourse());
                default:
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getStudentsInCourse());
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getUser($userId)
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
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getUser($userId));
                default:
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getUser($userId));
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }
    public function getUserEnrollments()
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
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getUserEnrollments());
                default:
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getUserEnrollments());
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getGradingStandards()
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
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getGradingStandards());
                default:
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getGradingStandards());
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }
    public function getCourse()
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
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getCourse());
                default:
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getCourse());
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getAccount($accountId)
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
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getAccount($accountId));
                default:
                    $canvasHelper = new CanvasHelper();
                    return ($canvasHelper->getAccount($accountId));
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }
    /*
     * PRIVATE METHODS
     */
    private function getModuleDataFromLms(ModulesRequest $request)
    {
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::MODULES);
                $canvas->getModuleData($request);
                return $this->dbHelper->getModuleData($request);
            default:
                $canvas = new CanvasHelper(DataType::MODULES);
                $canvas->getModuleData($request);
                return $this->dbHelper->getModuleData($request);
        }
    }

    private function getAssignmentDataFromLms(AssignmentsRequest $request)
    {
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentsRequest($request);
                return $this->dbHelper->getAssignmentData( $request);
            default:
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentsRequest($request);
                return $this->dbHelper->getAssignmentData( $request);
        }
    }

    private function getAssignmentGroupDataFromLms(AssignmentGroupsRequest $request)
    {
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentGroupsRequest($request);
                return $this->dbHelper->getAssignmentGroupData($request);
            default:
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentGroupsRequest($request);
                return $this->dbHelper->getAssignmentGroupData($request);
        }
    }

    private function getQuizzesFromLms(QuizRequest $request)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $quizzes = array();
            switch ($lms)
            {
                case (Lms::CANVAS):
                    $canvasHelper = new CanvasHelper();
                    $quizzes = $canvasHelper->getQuizzes();
                    if($request->getInclude_questions()&& !is_null($request->getId()))
                    {
                        $canvasHelper->getQuizQuestions($request->getId());
                    }
                    else if ($request->getInclude_questions())
                    {
                        foreach($quizzes as $quiz)
                        {
                            $canvasHelper->getQuizQuestions($quiz->quiz_id);
                        }
                    }
                    return $this->dbHelper->getQuizzes($request);
                default:
                    $canvasHelper = new CanvasHelper();
                    $quizzes = $canvasHelper->getQuizzes();
                    if($request->getInclude_questions()&& !is_null($request->getId()))
                    {
                        $canvasHelper->getQuizQuestions($request->getId());
                    }
                    else if ($request->getInclude_questions())
                    {
                        foreach($quizzes as $quiz)
                        {
                            $canvasHelper->getQuizQuestions($quiz->id);
                        }
                    }
                    return $this->dbHelper->getQuizzes($request);
            }
        }
        else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getQuizSubmission($quizId, $quizSubmissionId=null, $studentId=null)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        if(is_null($studentId))
        {
            $studentId = $_SESSION['userID'];
        }
        $quizSubmission = $this->dbHelper->getQuizSubmission($quizId, $studentId);
        if(is_null($quizSubmission))
        {//try to get it from the LMS
            $lms = strtoupper($_SESSION['lms']);
            if(Lms::isValidValue($lms))
            {
                switch ($lms)
                {
                    case (Lms::CANVAS):
                        $this->canvasHelper->getQuizSubmission($quizId,$quizSubmissionId);
                        return $this->dbHelper->getQuizSubmission($quizId, $studentId);
                }
            }else
            {
                throw new \Exception("Invalid LMS");
            }
        }
        else
        {
            return $quizSubmission;
        }
    }

    public function getQuizQuestion($quizId, $question_id = null)
    {
        $quizQuestion = $this->dbHelper->getQuizQuestion($quizId, $question_id);
        //if it wasn't on the DB, get it from the LMS
        if(is_null($quizQuestion)||(get_class($quizQuestion)=="Illuminate\Database\Eloquent\Collection"&&$quizQuestion->isEmpty()))
        {
            //get the quiz and questions from the LMS
            $req = new QuizRequest(ActionType::GET, $quizId, $fresh_data = true, true);
            $this->quizzes($req);
            return $this->dbHelper->getQuizQuestion($quizId);
        }
        else
        {
            return $quizQuestion;
        }
    }
    private function getQuizQuestionsFromLms($quizId, $questionId = null)
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
                    $this->canvasHelper->getQuizQuestions($quizId);
                    return $this->dbHelper->getQuizQuestion($quizId, $questionId);
            }
        }
    }
    public function postQuizTakingSession($quizId, $studentId)
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
                    $this->canvasHelper->postQuizTakingSession($quizId, $studentId);
                    return $this->dbHelper->getQuizSubmission($quizId, $studentId);
            }
        }else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function updateStudentQuizScore($quizId, $quizSubmission, array $questions, $totalPointsToFudge)
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
                    return $this->canvasHelper->updateStudentQuizScore($quizId, $quizSubmission, $questions, $totalPointsToFudge);
            }
        }else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function postTurnInQuiz($quizId, $quizSubmission)
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
                    return $this->canvasHelper->postTurnInQuiz($quizId, $quizSubmission);
            }
        }else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function isQuestionAnswered($quizId, $questionId, $quizSubmissionId)
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
                    return $this->canvasHelper->isQuestionAnswered($quizId, $questionId, $quizSubmissionId);
            }
        }else
        {
            throw new \Exception("Invalid LMS");
        }
    }

    public function getQuizSubmissionQuestions($quizSubmission)
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
                    return $this->canvasHelper->getQuizSubmissionQuestions($quizSubmission);
            }
        }else
        {
            throw new \Exception("Invalid LMS");
        }
    }
}
