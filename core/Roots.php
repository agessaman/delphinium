<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\Enums\CommonEnums\DataType;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\lmsClasses\Canvas;
use Delphinium\Core\Cache\CacheHelper;
use Delphinium\Core\Exceptions\InvalidActionException;

class Roots
{
    /*
     * Public Functions
     */
    
    public function modules(ModulesRequest $request)
    {
        switch($request->actionType)
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
                switch ($request->lms)
                {
                    case (Lms::CANVAS):
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->putModuleData($request);
                    default:
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->putModuleData($request);
                }
                break;
            case(ActionType::POST):
                switch ($request->lms)
                {
                    case (Lms::CANVAS):
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->postModuleData($request);
                    default:
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->postModuleData($request);
                }
                break;
            case(ActionType::DELETE):
                switch ($request->lms)
                {
                    case (Lms::CANVAS):
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->deleteModuleData($request);
                    default:
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->deleteModuleData($request);
                }
                break;
        }
        
    }
    
    public function submissions(SubmissionsRequest $request)
    {
        switch($request->actionType)
        {
            case(ActionType::GET):
                $result;
                switch ($request->lms)
                {
                    case (Lms::CANVAS):
                        $canvas = new Canvas(DataType::SUBMISSIONS);
                        $result = $canvas->processSubmissionsRequest($request);
                        break;
                    default:
                        $canvas = new Canvas(DataType::SUBMISSIONS);
                        $result = $canvas->processSubmissionsRequest($request);
                        break;

                }

                return $result;
                break; 
            default :
                throw new InvalidActionException($request->actionType, get_class($request));
        
        }
    }
    
    public function assignments(AssignmentsRequest $request)
    {
        switch($request->actionType)
        {
            case(ActionType::GET):
                $cacheHelper = new CacheHelper();
                $data = $cacheHelper->searchAssignmentDataInCache($request);
                if($data)
                {
                    return $data;
                }
                else
                {//if data is null it means it wasn't in cache... need to get it from Canvas
                    switch ($request->lms)
                    {
                        case (Lms::CANVAS):
                            $canvas = new Canvas(DataType::ASSIGNMENTS);
                            $canvas->processAssignmentsRequest($request);
                            return $cacheHelper->searchAssignmentDataInCache($request);
                        default:
                            $canvas = new Canvas(DataType::ASSIGNMENTS);
                            $canvas->processAssignmentsRequest($request);
                            return $cacheHelper->searchAssignmentDataInCache($request);

                    }
                }
            break;
        default :
            throw new InvalidActionException($request->actionType, get_class($request));
        }
    }
    
    public function assignmentGroups(AssignmentGroupsRequest $request)
    {
        switch($request->actionType)
        {
            case(ActionType::GET):
                $cacheHelper = new CacheHelper();
                $data = $cacheHelper->serchAssignmentGroupDataInCache($request);
                if($data)
                {//if data is null it means it wasn't in cache... need to get it from 
                    return $data;
                }
                else
                {
                    switch ($request->lms)
                    {
                        case (Lms::CANVAS):
                            $canvas = new Canvas(DataType::ASSIGNMENTS);
                            $canvas->processAssignmentGroupsRequest($request);
                            return $cacheHelper->serchAssignmentGroupDataInCache($request);
                        default:
                            $canvas = new Canvas(DataType::ASSIGNMENTS);
                            $canvas->processAssignmentGroupsRequest($request);
                            return $cacheHelper->serchAssignmentGroupDataInCache($request);
                    }
                }
                
            break;
        default :
            throw new InvalidActionException($request->actionType, get_class($request));
        }
    }
    
    private function getModuleDataFromLms(ModulesRequest $request)
    {
        $cacheHelper = new CacheHelper();
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new Canvas(DataType::MODULES);
                $canvas->getModuleData($request);
                return $cacheHelper->searchModuleDataInCache($request);
            default:
                $canvas = new Canvas(DataType::MODULES);
                $canvas->getModuleData($request);
                return $cacheHelper->searchModuleDataInCache($request);

        }
    }
}
