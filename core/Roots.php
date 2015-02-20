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
        switch ($request->lms)
        {
            case (Lms::CANVAS):
                $canvas = new Canvas(DataType::MODULES);
                $result = $canvas->processModuleRequest($request);
                break;
            default:
                $canvas = new Canvas(DataType::MODULES);
                $result = $canvas->processModuleRequest($request);
                break;

        }

        return $result;
    }
    
    public function submissions(SubmissionsRequest $request)
    {
        $result;
        switch ($request->lms)
        {
            case (Lms::Canvas):
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
    
    
    /*
     * Private Functions
     */
    
    private function getModuleData(ModulesRequest $request)
    {
        $courseId = $_SESSION['courseID'];
        
        
        $query = Module::query();
        $query->where('courseId','=',$courseId);
        
        if($request->moduleId)
        {
            $query->where('moduleId','=',$request->moduleId);
        }
        if($request->moduleItemId)
        {
            $query->where('contentId','=', $request->moduleItemId);
        }
        $results = $query->get();
        
        return $results;
        
    } 
}
