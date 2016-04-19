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

namespace Delphinium\Roots\DB;

use Delphinium\Roots\Models\ModuleItem;
use Delphinium\Roots\Models\Module;
use Delphinium\Roots\Models\Content;
use Delphinium\Roots\Models\Tag;
use Delphinium\Roots\Models\OrderedModule;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Models\Submission;
use Delphinium\Roots\Models\Quiz;
use Delphinium\Roots\Models\Quizquestion;
use Delphinium\Roots\Models\QuizSubmission;
use Delphinium\Roots\Models\AssignmentGroup;
use Delphinium\Roots\Models\UserCourse;
use Delphinium\Roots\Models\Role;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\QuizRequest;

class DbHelper
{
    /*
     * GET
     */
    public function getOrderedModuleByModuleId($courseId, $moduleId)
    {
        $orderedModule = OrderedModule::where('module_id', '=', $moduleId)->where('course_id', '=',$courseId)->first();
        return $orderedModule;
    }

    public function getTagsByContentId($content_id)
    {
        $content = Content::where('content_id', '=', $content_id)->first();
        return $content->tags;
    }

    public function getModuleData(ModulesRequest $request)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        if($request->getModuleId())
        {
            if($request->getModuleItemId())
            {
                return ModuleItem::with('content')->where(array(
                    'module_id' => $request->getModuleId(),
                    'module_item_id'=> $request->getModuleItemId()
                ))
                    ->orderBy('position', 'asc')
                    ->first();
            }
            else
            {
                return Module::with(array('module_items' =>
                    function($query) {
                        $query->orderBy('position', 'ASC');
                    },'module_items.content'))
                    ->where(array(
                        'module_id' => $request->getModuleId(),
                        'course_id' => $courseId
                    ))->first();
            }
        }
        else
        {//if no moduleId was found they must want all the modules

            $modules = Module::orderBy('parent_id', 'ASC')
                ->orderBy('order', 'ASC')
                ->with(array('module_items' => function($query) {
                    $query->orderBy('position', 'ASC');
                },'module_items.content'))
                ->where(array(
                    'course_id' => $courseId
                ))
                ->get();

            return $modules;
        }
    }

    public function getAssignmentData(AssignmentsRequest $request)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $courseId = $_SESSION['courseID'];

        $assignments;
        if($request->getAssignment_id())
        {//they want a specific assignment
            $assignments = Assignment::where(array(
                'assignment_id' => $request->getAssignment_id(),
                'course_id' => $courseId
            ))->first();
        }
        else
        {//return all assignments
            $assignments = Assignment::where(array(
                'course_id' => $courseId
            ))->get();
        }

        if(!$request->getIncludeTags())
        {
            if(!is_null($assignments))
            {
                return $assignments->toArray();
            }
            else
            {
                return [];
            }
        }
        else
        {
            $result = [];
            if(!is_null($assignments))
            {
                if (get_class($assignments)==="Illuminate\Database\Eloquent\Collection")
                {
                    foreach($assignments as $assignment)
                    {
                        $result[] = $this->matchAssignmentWithTags($assignment);
                    }
                }
                else
                {
                    $result[] = $this->matchAssignmentWithTags($assignments);
                }
            }
            return $result;
        }
    }

    public function getAssignmentGroupData(AssignmentGroupsRequest $request)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        if($request->getAssignment_group_id())
        {
            if($request->getInclude_assignments())
            {
                return AssignmentGroup::orderBy('position', 'ASC')
                    ->with(array('assignments' =>
                        function($query) {
                            $query->orderBy('position', 'ASC');
                        }))
                    ->where(array(
                        'assignment_group_id' => $request->getAssignment_group_id()
                    ))->first();
            }
            else
            {
                return AssignmentGroup::orderBy('position', 'ASC')
                    ->where(array(
                        'assignment_group_id' => $request->getAssignment_group_id()
                    ))->first();
            }

        }
        else
        {
            if($request->getInclude_assignments())
            {
                return AssignmentGroup::orderBy('position', 'ASC')
                    ->with(array('assignments' =>
                        function($query) {
                            $query->orderBy('position', 'ASC');
                        }))
                    ->where(array(
                        'course_id' => $courseId
                    ))->get();
            }
            else
            {
                return AssignmentGroup::orderBy('position', 'ASC')
                    ->where(array(
                        'course_id' => $courseId
                    ))->get();
            }
        }
    }

    public function getAvailableTags()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        $tags = Tag::where(array('course_id' => $courseId))->first();

        if((!is_null($tags))&&($tags->tags))
        {
            $possibleTags =  $tags->tags;
            if(strlen($possibleTags)>0)
            {
                $tagArr = explode(", ",$tags->tags);
                if(in_array("Optional", $tagArr)||in_array("optional", $tagArr)){
                }//doing !in_array gives false positives
                else
                {
                    array_push($tagArr,"Optional");
                }
                if (in_array("Description", $tagArr)||in_array("description", $tagArr)){
                }
                else
                {
                    array_push($tagArr,"Description");
                }
                return implode(", ",$tagArr);
            }
            else
            {
                return "Optional, Description";
            }
        }
        else
        {
            return "Optional, Description";
        }

    }

    public function getQuizzes(QuizRequest $request)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        if($request->getId())
        {
            if($request->getInclude_questions())
            {
                return Quiz::with('questions')->where(array(
                    'quiz_id' => $request->getId(),
                    'course_id'=> $courseId
                ))->first();

            }
            else
            {
                return Quiz::where(array(
                    'quiz_id' => $request->getId(),
                    'course_id'=> $courseId
                ))->first();
            }
        }
        else
        {//if no moduleId was found they must want all the modules
            if($request->getInclude_questions())
            {
                return Quiz::with('questions')->where(array(
                    'course_id' => $courseId
                ))->get();
            }
            else
            {
                return Quiz::where(array(
                    'course_id' => $courseId
                ))->get();
            }
        }
    }

    public function getQuizQuestion($quizId, $quizQuestionId=null)
    {
        if(is_null($quizQuestionId))
        {
            return Quizquestion::where(array(
                'quiz_id' => $quizId
            ))->get();
        }
        else
        {
            return Quizquestion::where(array(
                'quiz_id' => $quizId,
                'question_id'=> $quizQuestionId
            ))->first();
        }

    }
    /*
     * UPDATE
     */
    public function addTagsToContent($contentId, $newTagsStr, $courseId)
    {
        $content = Content::where('content_id', '=', $contentId)->first();

        if(!is_null($content))//this could be due to the moduleItem not having an Id
        {
            $newTags = explode(', ', $newTagsStr);
            $tagString = $this->mergeTagsUnique($newTagsStr, $content->tags);
            $content->tags =$tagString;
            $content->save();

            $this->updateAvailableTags($courseId, $newTags);
            return $content->tags;
        }
        else
        {
            return null;
        }
    }

    public function addTagsToAssignment($assignment, $newTagsStr, $courseId)
    {
        if(!is_null($assignment->assignment_id))
        {
            $assignment = Assignment::firstOrNew(array('assignment_id' => $assignment->assignment_id,'course_id'=>$courseId));
            $assignment->assignment_id = $assignment->assignment_id;
        }
        else if(!is_null($assignment->quiz_id))
        {
            $assignment = Assignment::firstOrNew(array('quiz_id' => $assignment->$assignment->quiz_id,'course_id'=>$courseId));
            $assignment->quiz_id = $assignment->quiz_id;
        }
        $assignment->course_id = $courseId;
        if(!is_null($assignment))//this could be due to the moduleItem not having an Id
        {
            $newTags = explode(', ', $newTagsStr);
            $tagString = $this->mergeTagsUnique($newTagsStr, $assignment->tags);
            $assignment->tags =$tagString;
            $assignment->save();

            $this->updateAvailableTags($courseId, $newTags);
            return $assignment->tags;
        }
        else
        {
            return null;
        }
    }

    public function updateContentTags($contentId, $newTagsStr, $courseId)
    {
        $content = Content::where('content_id', '=', $contentId)->first();

        if(!is_null($content))//
        {
            $content->tags =$newTagsStr;
            $content->save();

            $newTags = explode(', ', $newTagsStr);
            $this->updateAvailableTags($courseId, $newTags);
            return $content->tags;
        }
    }

    public function updateAssignmentTags($assignmentId, $newTagsStr, $courseId)
    {
        $assignment = Assignment::where('assignment_id', '=', $assignmentId)->where('course_id','=',$courseId)->first();

        if(!is_null($assignment))//
        {
            $assignment->tags =$newTagsStr;
            $assignment->save();

            $newTags = explode(', ', $newTagsStr);
            $this->updateAvailableTags($courseId, $newTags);
            return $assignment->tags;
        }
    }

    public function updateAvailableTags($courseId, $newTags)
    {
        $tags = Tag::firstOrNew(array('course_id' => $courseId));

        $possibleTags =  $tags->tags;
        if(strlen($possibleTags)>0)
        {
            $currentTagsArr = explode(', ', $possibleTags);
            $c = array_merge($currentTagsArr,$newTags);
            $unique = array_unique($c);
            $tagString =implode(', ', $unique);
        }
        else
        {
            $tagString =implode(', ', $newTags);
        }

        $tags->course_id = $courseId;
        $tags->tags = $tagString;
        $tags->save();

    }

    public function updateOrderedModule($module)
    {
        $orderedModule = OrderedModule::firstOrNew(
            array(
                'course_id' => $module->course_id,
                'module_id' => $module->module_id
            )
        );
        $orderedModule->module_id = $module->module_id;
        $orderedModule->parent_id = $module->parent_id;
        $orderedModule->course_id = $module->course_id;
        $orderedModule->order = $module->order;
        $orderedModule->save();

        $moduleDB = Module::where(array(
            'module_id' => $module->module_id,
            'course_id' => $module->course_id
        ))->first();

        $moduleDB->parent_id = $module->parent_id;
        $moduleDB->order = $module->order;
        $moduleDB->save();

        return $orderedModule;
    }


    /*
     * DELETE
     */
    public function deleteTag($contentId, $tag)
    {
        $content = Content::where('content_id', '=', $contentId)->first();

        $currTagStr = $content->tags;

        $current = explode(', ', $currTagStr);

        $new = array();
        $new[] = $tag;
        $filtered = array_diff($current, $new);


        $tagString =implode(', ', $filtered);

        $content->tags = $tagString;
        $content->save();
        return $content->tags;
    }

    //These cascading delete methods exist because OctoberCMS doesn't support cascading delete yet. 
    //Inn https://github.com/octobercms/october/issues/419 it says that the bug has been fixed and the code commited, 
    //but I just downloaded the RC version of OctoberCMS and it doesn't include that fix (at least the cascading delete isn't
    //happening). -DZ 3/26/2015
    public function deleteAllModuleItemsByModuleIdCascade($moduleId)
    {
        $moduleItems = ModuleItem::where('module_id','=',$moduleId)->get();
        //delete all content
        foreach($moduleItems as $item)
        {
            $this->deleteAllContentByModuleItem($item->module_item_id);
        }

        ModuleItem::where('module_id','=',$moduleId)->delete();
    }

    public function deleteAllContentByModuleItem($moduleItemId)
    {
        Content::where('module_item_id','=', $moduleItemId)->delete();
    }

    public function deleteModuleCascade($courseId, $moduleId)
    {
        $this->deleteAllModuleItemsByModuleIdCascade($moduleId);

        Module::where('course_id', '=', $courseId)
            ->where('module_id','=',$moduleId)->delete();
    }

    public function deleteModuleItemCascade($moduleId, $moduleItemId)
    {
        //delete the this module item's content
        $this->deleteAllContentByModuleItem($moduleItemId);

        //delete the actual ModuleItem
        ModuleItem::where('module_item_id', '=', $moduleItemId)
            ->where('module_id','=',$moduleId)->delete();
    }

    public function qualityAssuranceModules($courseId, $currenModuleIdsArr)
    {
        $modules = Module::where('course_id','=',$courseId)->select('module_id')->get();
        $fromDBArr = array();
        foreach($modules as $mod)
        {
            $fromDBArr[] = $mod['module_id'];
        }

        $toBeDeleted =array_diff($fromDBArr,$currenModuleIdsArr);

        foreach($toBeDeleted as $module)
        {//TODO: verify cascading delete
            Module::where('course_id','=',$courseId)->where('module_id','=',  intval($module))->delete();
        }
    }

    public function qualityAssuranceModuleItems($courseId, $moduleItemIds)
    {
        $modulesItems = ModuleItem::where('course_id','=',$courseId)->select('module_item_id')->get();
        $fromDBArr = array();
        foreach($modulesItems as $item)
        {
            $fromDBArr[] = $item['module_item_id'];
        }

        $toBeDeleted =array_diff($fromDBArr,$moduleItemIds);

        foreach($toBeDeleted as $module_item_id)
        {
            ModuleItem::where('course_id','=',$courseId)->where('module_item_id','=',  intval($module_item_id))->delete();
        }
    }

    public function getAssignment($assignment_id)
    {
        return Assignment::where(array('assignment_id' => $assignment_id))->first();
    }

    public function matchSubmissionWithTags(Submission $submission)
    {
        $assignment = Assignment::where(array('assignment_id' => $submission->assignment_id));
        if(is_null($assignment))
        {
            return null;
        }
        else
        {
            $assignment->assignment_id = $submission->assignment_id;
            $assignmentWithTags = $this->matchAssignmentWithTags($assignment);

            $arr = $submission->toArray();
            if(strlen($assignmentWithTags['tags'])>0)
            {
                $arr['tags'] = $assignmentWithTags['tags'];
            }
            else
            {
                $arr['tags'] = "";
            }
            return $arr;
        }
    }

    public function matchAssignmentWithTags(Assignment $assignment)
    {
        $content;
        if(!is_null($assignment->quiz_id))
        {//quiz_id will be equal to content_id from a module_item_id
            $content = Content::where(array(
                'content_id' => $assignment->quiz_id
            ))->first();
        }
        else
        {
            $content = Content::where(array(
                'content_id' => $assignment->assignment_id
            ))->first();
        }

        $arr = $assignment->toArray();
        if(!is_null($content))
        {
            $arr['tags'] = $content->tags;
        }
        else
        {
            $arr['tags'] = "";
        }
        return $arr;
    }

    public function getAssignmentTags($assignment_id)
    {
        $content = Content::where(array(
            'content_id' => $assignment_id
        ))->first();
        if(!is_null($content))
        {
            return $content->tags;
        }
        else
        {
            return "";
        }
    }

    public function getQuizSubmission($quiz_id, $user_id)
    {
        $quizSubmission = QuizSubmission::where(array(
            'user_id' => $user_id,
            'quiz_id'=> $quiz_id
        ))->first();

        return $quizSubmission;
    }

    public function getUserInCourse($courseId, $userId)
    {
        $user = UserCourse::with('user')->where(array(
            'user_id' => $userId,
            'course_id'=> $courseId
        ))->first();

        return $user;
    }

    public function getUsersInCourseWithRole($courseId, $role_name)
    {
        $role = $this->getRole($role_name);
        $users = UserCourse::with('user')->where(array(
            'course_id'=> $courseId,
            'role'=>$role->id
        ))->get();

        return $users;
    }

    public function getCondensedUsersInCourseWithRole($courseId, $role_name)
    {
        $role = $this->getRole($role_name);
        $users = UserCourse::with(array('user'=>function($query){
            $query->select('user_id','name');
        }))
            ->where(array(
                'course_id'=> $courseId,
                'role'=>$role->id
            ))->get(['user_id']);

        return $users;
    }
    public function getCourseApprover($courseId)
    {
        $role = Role::where('role_name','=','Approver')->first();
        $user = UserCourse::with('user')->where(array(
            'role' => $role->id,
            'course_id'=> $courseId
        ))->first();

        return $user;
    }

    public function deleteUserFromRole($courseId, $userId, $role_name)
    {
        $role = Role::where('role_name','=',$role_name)->first();
        $user = UserCourse::where(array(
            'role' => $role->id,
            'course_id'=> $courseId,
            'user_id' => $userId
        ))->first();

        $user->delete();
    }

    public function getRole($role_name)
    {
        return Role::where('role_name','=',$role_name)->first();
    }
}
