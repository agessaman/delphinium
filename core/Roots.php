<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
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
                $cacheHelper = new CacheHelper();
                $data = $cacheHelper->searchModuleDataInCache($request);
                if($data)
                {//if data is null it means it wasn't in cache... need to get it from 
                    return $data;
                }
                else
                {
                    switch ($request->lms)
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
                
                
            case(ActionType::PUT):
                $response;
                switch ($request->lms)
                {
                    case (Lms::CANVAS):
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->putModuleData($request);
                    default:
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->putModuleData($request);
                }
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
                $response;
                switch ($request->lms)
                {
                    case (Lms::CANVAS):
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->deleteModuleData($request);
                    default:
                        $canvas = new Canvas(DataType::MODULES);
                        return $canvas->deleteModuleData($request);
                }
        }
        
    }
    
    public function submissions(SubmissionsRequest $request)
    {
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
    }
    
    public function assignments(AssignmentsRequest $request)
    {
        switch($request->actionType)
        {
            case(ActionType::GET):
                switch ($request->lms)
                {
                    case (Lms::CANVAS):
                        $canvas = new Canvas(DataType::ASSIGNMENTS);
                        $result = $canvas->processAssignmentsRequest($request);
                        break;
                    default:
                        $canvas = new Canvas(DataType::ASSIGNMENTS);
                        $result = $canvas->processAssignmentsRequest($request);
                        break;

                }
            break;
        default :
            throw new InvalidActionException($request->actionType, get_class($request));
        }
    }
    
    
    
}
