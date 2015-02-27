<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\Enums\CommonEnums\DataType;
use Delphinium\Core\Exceptions\RequestObjectException;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Core\lmsClasses\Canvas;
use Delphinium\Raspberry\Models\Module;

class Roots
{
    /*
     * Public Functions
     */
    
    public function modules(ModulesRequest $request)
    {
        $result;
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
                    $result = $cacheHelper->searchModuleDataInCache($request);
                    break;
                default:
                    $canvas = new Canvas(DataType::MODULES);
                    $canvas->getModuleData($request);
                    $result = $cacheHelper->searchModuleDataInCache($request);
                    break;

            }
        }
        return $result;
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
        return true;
    }
    
}
