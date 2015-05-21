<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\Enums\CommonEnums\DataType;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\ModuleItemEnums\ModuleItemType;
use Delphinium\Core\lmsClasses\CanvasHelper;
use Delphinium\Core\Cache\CacheHelper;
use Delphinium\Core\Exceptions\InvalidActionException;
use Delphinium\Core\DB\DbHelper;

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
                    $dbHelper = new DbHelper();
                    $data = $dbHelper->getModuleData($request);
                    
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
                    $dbHelper = new DbHelper();
                    $data = $dbHelper->getAssignmentData( $request);
                    switch(get_class($data))
                    {
                        case "Illuminate\Database\Eloquent\Collection":
                            return (!$data->isEmpty()) ?  $data :  $this->getAssignmentDataFromLms($request);
                        default:
                            return (!is_null($data)) ? $data : $this->getAssignmentDataFromLms($request);
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
                    return $this->getAssignmentGroupDataFromLms( $request);
                }
                
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
        $dbHelper = new DbHelper();
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
            $orderedModule = $dbHelper->updateOrderedModule($item);
            array_push($ordered, $orderedModule->toArray());
        }
        return $ordered;
    }
    
    public function getAvailableTags()
    {
        $dbHelper = new DbHelper();
        return $dbHelper->getAvailableTags();
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
    
    public function getModuleItemTypes()
    {
        return ModuleItemType::getConstants();
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
                    $files = json_decode($canvasHelper->getFiles());
                    break;
                default:
                    $canvasHelper = new CanvasHelper();
                    $files = json_decode($canvasHelper->getFiles());
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
                    $pages = json_decode($canvasHelper->getPages());
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
    
    public function getQuizzes()
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
                    $quizzes = json_decode($canvasHelper->getQuizzes());
                    break;
                default:
                    $canvasHelper = new CanvasHelper();
                    $quizzes = json_decode($canvasHelper->getQuizzes());
                    break;
            }
            $return =array();
            $i=0;
            foreach($quizzes as $item)
            {
                $file = new \stdClass();

                $file->id = $item->id;
                $file->name=$item->title;
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
                    $tools =  json_decode($canvasHelper->getExternalTools());
                    break;
                default:
                    $canvasHelper = new CanvasHelper();
                    $tools = json_decode($canvasHelper->getExternalTools());
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
            
    
    /*
     * PRIVATE METHODS
     */
    private function getModuleDataFromLms(ModulesRequest $request)
    {
        $dbHelper = new DbHelper();
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::MODULES);
                $canvas->getModuleData($request);
                return $dbHelper->getModuleData($request);
            default:
                $canvas = new CanvasHelper(DataType::MODULES);
                $canvas->getModuleData($request);
                return $dbHelper->getModuleData($request);
        }
    }
    
    private function getAssignmentDataFromLms(AssignmentsRequest $request)
    {
        $dbHelper = new DbHelper();
        switch ($request->getLms())
        {
            case (Lms::CANVAS):
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentsRequest($request);
                return $dbHelper->getAssignmentData( $request);
            default:
                $canvas = new CanvasHelper(DataType::ASSIGNMENTS);
                $canvas->processAssignmentsRequest($request);
                return $dbHelper->getAssignmentData( $request);
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
