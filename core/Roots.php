<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\Enums\CommonEnums\DataType;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\lmsClasses\CanvasHelper;
use Delphinium\Core\Cache\CacheHelper;
use Delphinium\Core\Exceptions\InvalidActionException;

class Roots
{
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
                    
                    $cacheHelper = new CacheHelper();
                    $data = $cacheHelper->searchModuleDataInCache($request);
                    if($data)
                    {//if data is null it means it wasn't in cache... need to get it from LMS
                        return $data;
                    }
                    else
                    {
                        return $this->getModuleDataFromLms($request);
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
    
    public function submissions(SubmissionsRequest $request)
    {
        switch($request->getActionType())
        {
            case(ActionType::GET):
                $result;
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
                break; 
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
                    $cacheHelper = new CacheHelper();
                    $data = $cacheHelper->searchAssignmentDataInCache($request);
                    if($data)
                    {//if data is null it means it wasn't in cache... need to get it from LMS
                        return $data;
                    }
                    else
                    {
                        return $this->getAssignmentDataFromLms($request);
                    }
                }
                else
                {
                    return $this->getAssignmentDataFromLms($request);
                }
                break;
                //If another action was given throw exception
            default :
                throw new InvalidActionException($request->getActionType(), get_class($request));
        }
    }
    
    public function assignmentGroups(AssignmentGroupsRequest $request)
    {
        switch($request->getActionType())
        {
            case(ActionType::GET):
                if(!$request->getFresh_data())
                {
                    $cacheHelper = new CacheHelper();
                    $data = $cacheHelper->serchAssignmentGroupDataInCache($request);
                    if($data)
                    {//if data is null it means it wasn't in cache... need to get it from 
                        return $data;
                    }
                    else
                    {
                        return $this->getAssignmentGroupDataFromLms( $request);
                    } 
                }
                else
                {
                    return $this->getAssignmentGroupDataFromLms( $request);
                }
                
            break;
        default :
            throw new InvalidActionException($request->getActionType(), get_class($request));
        }
    }
    
    private function getModuleDataFromLms(ModulesRequest $request)
    {
        $cacheHelper = new CacheHelper();
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::MODULES);
                $canvas->getModuleData($request);
                return $cacheHelper->searchModuleDataInCache($request);
            default:
                $canvas = new CanvasHelper(DataType::MODULES);
                $canvas->getModuleData($request);
                return $cacheHelper->searchModuleDataInCache($request);
        }
    }
    
    
    private function getAssignmentDataFromLms(AssignmentsRequest $request)
    {
        $cacheHelper = new CacheHelper();
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentsRequest($request);
                return $cacheHelper->searchAssignmentDataInCache($request);
            default:
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentsRequest($request);
                return $cacheHelper->searchAssignmentDataInCache($request);

        }
    }
    
    private function getAssignmentGroupDataFromLms(AssignmentGroupsRequest $request)
    {
        $cacheHelper = new CacheHelper();
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentGroupsRequest($request);
                return $cacheHelper->serchAssignmentGroupDataInCache($request);
            default:
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentGroupsRequest($request);
                return $cacheHelper->serchAssignmentGroupDataInCache($request);
        }
    }
}
