<?php namespace Delphinium\Roots;

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
use Delphinium\Roots\Cache\CacheHelper;
use Delphinium\Roots\Exceptions\InvalidActionException;
use Delphinium\Roots\DB\DbHelper;

class Roots
{
    public $dbHelper;
    
    function __construct() 
    {
        $this->dbHelper = new DbHelper();
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
    
    public function assignmentGroups(AssignmentGroupsRequest $request)
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
                    $data = json_decode($canvasHelper->getAnalyticsAssignmentData());
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
                    $data = json_decode($canvasHelper->getAnalyticsAssignmentData());
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
                    $data = json_decode($canvasHelper->getAnalyticsStudentAssignmentData($userId));
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
                    $data = json_decode($canvasHelper->getAnalyticsStudentAssignmentData($userId));
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
                    return json_decode($canvasHelper->getUsersInCourse());
                default:
                    $canvasHelper = new CanvasHelper();
                    return json_decode($canvasHelper->getUsersInCourse());
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
                    return json_decode($canvasHelper->getUserEnrollments());
                default:
                    $canvasHelper = new CanvasHelper();
                    return json_decode($canvasHelper->getUserEnrollments());
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
                    return json_decode($canvasHelper->getGradingStandards());
                default:
                    $canvasHelper = new CanvasHelper();
                    return json_decode($canvasHelper->getGradingStandards());
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
                    return json_decode($canvasHelper->getAccount($accountId));
                default:
                    $canvasHelper = new CanvasHelper();
                    return json_decode($canvasHelper->getAccount($accountId));
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
    
}
