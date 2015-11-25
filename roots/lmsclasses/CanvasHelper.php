<?php namespace Delphinium\Roots\Lmsclasses;

use \DateTime;
use Delphinium\Roots\DB\DbHelper;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Roots\Exceptions\InvalidRequestException;
use Delphinium\Roots\Guzzle\GuzzleHelper;
use Delphinium\Roots\Models\ModuleItem;
use Delphinium\Roots\Models\Content;
use Delphinium\Roots\Models\Module;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Models\AssignmentGroup;
use Delphinium\Roots\Models\Submission;
use Delphinium\Roots\Models\Page;
use Delphinium\Roots\Models\File;
use Delphinium\Roots\Models\Quiz;
use Delphinium\Roots\Models\Quizquestion;
use Delphinium\Roots\Models\QuizSubmission;
use Delphinium\Roots\Models\Discussion;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Updatableobjects\Module as UpdatableModule;
use Delphinium\Roots\Updatableobjects\ModuleItem as UpdatableModuleItem;

class CanvasHelper
{
    public $dbHelper;
    
    function __construct() 
    {
        $this->dbHelper = new DbHelper();
    }
    /*
     * public functions
     */
    /*
     * MODULES
     */
    
    public function getModuleStates($request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $userId = $_SESSION['userID'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];

        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";
        $urlPieces[] = 'modules';
        
        $urlArgs[] = "student_id={$userId}";
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
        $response = GuzzleHelper::makeRequest($request, $url);

        $moduleStateInfo = array();
        $states = json_decode($response->getBody());
        
        foreach($states as $moduleRow)
        {
            //we'll create an array with all the moduleIds that belong to this courseId
            $mod = new \stdClass();
            $mod->module_id = $moduleRow->id;
            $mod->state = $moduleRow->state;
            if(isset($moduleRow->completed_at)){$mod->completed_at = $moduleRow->completed_at;}
            array_push($moduleStateInfo, $mod);
        }

        return $moduleStateInfo;
        
    }
    
    public function getFiles()
    {
        return $this->simpleGet('files');
    }
    
    public function getPages()
    {
        return $this->simpleGet('pages');
    }
    
    public function getQuizSubmission($quizId, $quizSubmissionId=null)
    {///api/v1/courses/:course_id/quizzes/:quiz_id/submissions/:id
        $urlPieces= $this->initUrl();
        if(!isset($_SESSION))
        {
            session_start();
        }
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'quizzes';
        $urlPieces[] = $quizId;
        $urlPieces[] = 'submissions';
        if(!is_null($quizSubmissionId))
        {
            $urlPieces[] = $quizSubmissionId;
        }
        
        $urlArgs[]="access_token={$token}&per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        
        $response = GuzzleHelper::getAsset($url);
        $items = json_decode($response->getBody());
        $arr = array();
        if(count($items->quiz_submissions)>0)
        {
            foreach($items->quiz_submissions as $item)
            {
                $item = $this->saveQuizSubmission($item);
                if(count($items->quiz_submissions)>1)
                {
                    $arr[] = $item;
                }
                else
                {
                    return $item;
                }
                return $arr;
            }
        }
        else
        {
            return null;
        }
    }
    
    public function postAnswerQuestion($quizSubmission, $questionsWrap)
    {
        
    }
    public function postQuizTakingSession($quizId)
    {///ap i/v1/courses/:course_id/quizzes/:quiz_id/submissions
        $urlPieces= $this->initUrl();
        if(!isset($_SESSION))
        {
            session_start();
        }
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'quizzes';
        $urlPieces[] = $quizId;
        $urlPieces[] = 'submissions';
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
       
        try
        {
            $response = GuzzleHelper::postData($url);
            $items = json_decode($response->getBody());
            
            if(count($items->quiz_submissions)>0)
            {
                return $this->saveQuizSubmission($items->quiz_submissions[0]);
            }
            else
            {
                $action = "start a quiz taking session";
                $exception = new InvalidRequestException($action, "unknown error", 400);
            }
        } 
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $code = $e->getCode();
            $action = "start a quiz taking session";
            
            switch($code)
            {
                case 400:
                    $exception = new InvalidRequestException($action, "the quiz is locked", 400);
                    throw $exception;
                case 403:
                    $exception = new InvalidRequestException($action, "the access code was invalid, or the IP is restricted", 403);
                    throw $exception;
                case 409:
                    $exception = new InvalidRequestException($action, "a quiz submission already exists for this user", 409);
                    throw $exception;
            }
            
        }
    }
    
    public function isQuestionAnswered($quizId, $questionId, $quizSubmissionId)
    {///api/v1/quiz_submissions/:quiz_submission_id/questions
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlPieces= array();
        $urlPieces[]= "https://{$domain}/api/v1/quiz_submissions/{$quizSubmissionId}/questions";
        
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        $response = GuzzleHelper::getAsset($url);
        $items= json_decode($response->getBody());
        
        foreach($items->quiz_submission_questions as $submission)
        {
            $question = $submission->id === $questionId && $quizId === $submission->quiz_id;
            $isAnswered = !is_null($submission->answer) && isset($submission->answer);
            if(($question) && ($isAnswered))
            {
                return true;
            }
            else
            {
                return false;
            }    
        }
    }
    
    public function postTurnInQuiz($quizId, QuizSubmission $quizSubmission)
    {///api/v1/courses/:course_id/quizzes/:quiz_id/submissions/:id/complete
        $urlPieces= $this->initUrl();
        if(!isset($_SESSION))
        {
            session_start();
        }
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'quizzes';
        $urlPieces[] = $quizId;
        $urlPieces[] = 'submissions';
        $urlPieces[] = $quizSubmission->quiz_submission_id;
        $urlPieces[] = 'complete';
        
        $urlArgs[]="attempt={$quizSubmission->attempt}";
        $urlArgs[]="validation_token={$quizSubmission->validation_token}";
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
       
        try
        {
            $response = GuzzleHelper::postData($url);
            $items = json_decode($response->getBody());
            
            if(count($items->quiz_submissions)>0)
            {
                return $this->saveQuizSubmission($items->quiz_submissions[0]);
            }
            else
            {
                $action = "turn in quiz";
                $exception = new InvalidRequestException($action, "unknown error", 400);
            }
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $code = $e->getCode();
            $action = "turn in a quiz";
            
            switch($code)
            {
                case 400:
                    $exception = new InvalidRequestException($action, "the quiz has already been turned in; "
                            . "or the attempt parameter is incorrect or missing", 400);
                    throw $exception;
                case 403:
                    $exception = new InvalidRequestException($action, "the access code or token was invalid, or the IP is restricted", 403);
                    throw $exception;
            }
        }
        
    }
    public function getQuizzes()
    {
        //process quiz
        $res = $this->simpleGet('quizzes');
        if(!isset($_SESSION))
        {
        session_start();
        }
        $courseId = $_SESSION['courseID'];
        $quizzes = json_decode($res);
        $response = array();
        foreach($quizzes as $quiz)
        {
            $response[] = $this->processSingleQuiz($quiz, $courseId);
        }
        
        return $response;
    }
    
    public function getQuizQuestions($quizId)
    {//api/v1/courses/:course_id/quizzes/:quiz_id/questions
       $urlPieces= $this->initUrl();
        if(!isset($_SESSION))
        {
            session_start();
        }
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'quizzes';
        $urlPieces[] = $quizId;
        $urlPieces[] = 'questions';
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        $response = GuzzleHelper::getAsset($url);
        $questions = json_decode($response->getBody());

        $response = array();
        foreach($questions as $question)
        {
            $response[] = $this->processSingleQuizQuestion($question);
        }
        return $response;
    }
    
    public function getExternalTools()
    {
        return $this->simpleGet('external_tools');
    }
    
    public function getModuleData(ModulesRequest $request)
    {   
        $moduleStates = false;
        //As per Jared's & Damaris' discussion when users request fresh module data we wil retrieve ALL module data so we can store it in 
        //DB and then we'll only return the data they asked for
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];

        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $urlPieces[] = 'modules';
        $urlArgs[] = 'include[]=items';
        $urlArgs[]= 'include[]=content_details';
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
        
        $response = GuzzleHelper::makeRequest($request, $url);
        
        return $this->processCanvasModuleData(json_decode($response->getBody()), $courseId);
    }
    
    public function putModuleData(ModulesRequest $request)
    {   
        $updateCanvas = false;
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];
        $scope = "module";
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $urlPieces[] = "modules/{$request->getModuleId()}";
        
        if($request->getModuleItem())
        {
            $tags = $request->getModuleItem()->getTags();

            if($tags)
            {
                $dbHelper = new DbHelper();
                return $dbHelper->updateContentTags($request->getModuleItem()->content_id, $tags, $courseId);
            }
        }
        if($request->getModuleItemId())
        {//updating a module item
            $updateCanvas = true;
            $urlPieces[] = "items/{$request->getModuleItemId()}";
            $scope = "module_item";
            $urlArgs = $this->buildModuleItemUpdateArgs($request->getModuleItem());
        }
        else if($request->getModuleId())
        {//updating a module
            $updateCanvas = true;
            $urlArgs = $this->buildModuleUpdateArgs($request->getModule());
        }
        
        if($updateCanvas)
        {
            //Attach token
            $urlArgs[]="access_token={$token}";

            $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 

            $response = GuzzleHelper::makeRequest($request, $url);

            //update DB if request was successful
            if ($response->getStatusCode() ==="200")
            {
                $newlyUpdated= \GuzzleHttp\json_decode($response->getBody());

                if(isset($newlyUpdated->module_id))
                {
                    //it's a module item
                    return $this->processSingleModuleItem($courseId, $newlyUpdated);

                }
                else 
                {
                    //it's a module
                    return $this->processSingleModule($newlyUpdated, $courseId);
                }
            }
        }
        
    }
    
    public function deleteModuleData(ModulesRequest $request)
    {
        $isModuleItem = false;
        if(!$request->getModuleId())
        {
            throw new InvalidParameterInRequestObjectException(get_class($request),"moduleId", "Parameter is required");
        }
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];
        $scope = "module";
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $urlPieces[] = "modules/{$request->getModuleId()}";
        
        if($request->getModuleItemId())
        {
            $isModuleItem = true;
            $urlPieces[] = "items/{$request->getModuleItemId()}";
            $scope = "module_item";
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 

        try
        {
            //delete from Canvas
            $response = GuzzleHelper::makeRequest($request, $url);
            if($response->getStatusCode() ==="200")
            {
                $dbHelper = new DbHelper();
                /*
                 * NOTE:
                 * Cascading delete is not yet supported in OctoberCMS, so we have to do all the cascading deletes manually. 
                 * See https://github.com/octobercms/october/issues/419
                 */
                if($isModuleItem)
                {
                    //delete the module item and its contents from DB
                    $dbHelper->deleteModuleItemCascade($request->getModuleId(), $request->getModuleItemId());
                     //delete module item's contents
    //                $this->deleteModuleItemsContent($courseId, $request->moduleId, $request->moduleItemId);
                }
                else
                {//DELETE MODULE

                //this will delete this module, its module items, and the content from DB
                    $dbHelper->deleteModuleCascade($courseId, $request->getModuleId());
                }
            }
            
              
            return $response;
        }
        catch(\GuzzleHttp\Exception\ClientException $e)//without the backslash the Exception won't be caught!
        {
            if ($e->hasResponse()) 
            {
                if ($e->getResponse()->getStatusCode() ==="404")
                { //This can be caused because the module/moduleItem doesn't exist. Just return
                    return null;
                }
            }
            return "An error occurred. Unable to delete module data";
        }

    }
    
    public function postModuleData(ModulesRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];
        $scope = "module";
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        
        if($request->getModuleId())
        {// "we're creating a moduleItem";
            
            $urlPieces[] = "modules/{$request->getModuleId()}/items";
            $urlArgs = $this->buildAddModuleItemArgs($request);
        }
        else
        {//we're creating a module obj
        
            $urlPieces[] = "modules";
            $urlArgs = $this->buildAddModuleArgs($request);
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
//        echo "The URL is".$url."--";
        //return;
        $response = GuzzleHelper::makeRequest($request, $url);
        
        //update DB if request was successful
        if ($response->getStatusCode() ==="200")
        {
            $newlyCreated= \GuzzleHttp\json_decode($response->getBody());
            $newFromDb;
            if(isset($newlyCreated->module_id))
            {
                //it's a module item
                        
                $this->processSingleModuleItem($courseId, $newlyCreated);
                $newFromDb = ModuleItem::with('content')->where(array(
                    'module_id' => $newlyCreated->module_id,
                    'module_item_id'=> $newlyCreated->id
                ))->first();
//                echo json_encode($modItem);
                if($request->getModuleItem()->getTags())
                {//add the tags!
                    $tags = $request->getModuleItem()->getTags();
                    
                    $dbHelper = new DbHelper();
                    $dbHelper->addTagsToContent($modItem['content_id'], $tags, $courseId);
                    
                }
            }
            else 
            {
                //it's a module
                $this->processSingleModule($newlyCreated, $courseId);
                $newFromDb = Module::firstOrNew(array('module_id' => $newlyCreated->id));
            }
            
            return $newFromDb;
        }
        else
        {
            return 0;
        }
    }
     
    public function addPage(Page $page)
    {///api/v1/courses/:course_id/pages
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'pages';
        
        foreach($page as $key => $value) 
        {
            if ($value)
            {
                $urlArgs[] = "wiki_page[{$key}]={$value}";
            }
        }
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        $response = GuzzleHelper::postData($url);
        return $response->getBody();
    }
    
    public function addDiscussion(Discussion $discussion)
    {///api/v1/courses/:course_id/discussion_topics
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'discussion_topics';
        
        foreach($discussion as $key => $value) 
        {
            if ($value)
            {
                $urlArgs[] = "{$key}={$value}";
            }
        }
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        $response = GuzzleHelper::postData($url);
        return $response->getBody();
    }
    
    public function addAssignment(AssignmentsRequest $request)
    {
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = "assignments";
        
        foreach($request->getAssignment()->attributes as $key => $value) {
            if ($value)
            {
                if(($key==="due_at"||$key==="unlock_at"||$key=="lock_at"))
                {
                    $urlArgs[] = "assignment[{$key}]={$value->format('c')}";
                    continue;
                }
                if($key==="points_possible")
                {
                    $urlArgs[] = "assignment[{$key}]=".floatval($value);
                    continue;
                }
                $urlArgs[] = "assignment[{$key}]={$value}";
            }
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
//        echo $url;
//        return;
        $response = GuzzleHelper::makeRequest($request, $url);
        return json_decode($response->getBody());
    }
    
    public function updateAssignment(AssignmentsRequest $request)
    {
        $urlPieces= $this->initUrl();
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];
        $urlArgs = array();
        $urlPieces[] = "assignments";
        
//        foreach($request->getAssignment()->attributes as $key => $value) {
//            if ($value)
//            {
//                if(($key==="due_at"||$key==="unlock_at"||$key=="lock_at"))
//                {
//                    $urlArgs[] = "assignment[{$key}]={$value->format('c')}";
//                    continue;
//                }
//                if($key==="points_possible")
//                {
//                    $urlArgs[] = "assignment[{$key}]=".floatval($value);
//                    continue;
//                }
//                if($key==="tags")
//                {
//                    continue;
//                }
//                $urlArgs[] = "assignment[{$key}]={$value}";
//            }
//        }
//        
//        //Attach token
//        $urlArgs[]="access_token={$token}";
//
//        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
////        echo $url;
////        return;
//        $response = GuzzleHelper::makeRequest($request, $url);
//        $body = json_decode($response->getBody());
//        
        
        $tags = $request->getAssignment()->tags;
        $dbHelper = new DbHelper();
        $dbHelper->addTagsToAssignment($request->getAssignment(), $tags, $courseId);
                    
    }
    
    
    public function addQuiz(Quiz $quiz)
    {///api/v1/courses/:course_id/discussion_topics
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'quizzes';
        
        foreach($quiz as $key => $value) 
        {
            if ($value)
            {
                if($key==='due_at')
                {
                    $urlArgs[] = "quiz[{$key}]={$value->format('c')}";
                    continue;
                }
                $urlArgs[] = "quiz[{$key}]={$value}";
            }
        }
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
//        echo $url;
//        return;
        $response = GuzzleHelper::postData($url);
        return json_decode($response->getBody());
    }
    
    public function addExternalTool($externalTool)
    {
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'external_tools';
        
        foreach($externalTool as $key => $value) 
        {
            if ($value)
            {
                $urlArgs[] = "{$key}={$value}";
            }
        }
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
//        echo $url;
        $response = GuzzleHelper::postData($url);
        return json_decode($response->getBody());
    }
    public function uploadFile(File $file)
    {
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlPieces[] = 'files';
        $urlArgs = array();
        
        foreach($file as $key => $value) 
        {
            if ($value)
            {
                $urlArgs[] = "{$key}={$value}";
            }
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
//        echo $url;
        $response = GuzzleHelper::postData($url);
        return $response->getBody();
    }
    
    public function uploadFileStepTwo($params, $file, $upload_url)
    {
        return GuzzleHelper::postMultipartRequest($params, $file, $upload_url);
    }
    
    public function uploadFileStepThree($location)
    {
        $urlPieces= $location;
        $urlArgs = array();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
//        echo $url;
        $response = GuzzleHelper::postData($url);
        return json_decode($response->getBody());
    }
    
    /*
     * Quizzes
     */
    public function processSingleQuiz($item, $courseId)
    {   
        $quiz = Quiz::firstOrNew(array('quiz_id' => $item->id));
        $quiz->quiz_id = $item->id;
        $quiz->course_id = $courseId;
        if(isset($item->title)){$quiz->title = $item->title;}
        if(isset($item->description)){$quiz->description = $item->description;}
        if(isset($item->html_url)){$quiz->html_url = $item->html_url;}
        if(isset($item->quiz_type)){$quiz->quiz_type = $item->quiz_type;}
        if(isset($item->assignment_group_id)){$quiz->assignment_group_id = $item->assignment_group_id;}
        if(isset($item->time_limit)){$quiz->time_limit = $item->time_limit;}
        if(isset($item->question_count)){$quiz->question_count = $item->question_count;}
        if(isset($item->points_possible)){$quiz->points_possible = $item->points_possible;}
        if(isset($item->due_at)){
            $due_at= DateTime::createFromFormat(DateTime::ISO8601, $item->due_at);
            $quiz->due_at = $due_at->format('c');
        }
        if(isset($item->lock_at)){
            $lock_at= DateTime::createFromFormat(DateTime::ISO8601, $item->lock_at);
            $quiz->lock_at = $lock_at->format('c');
        }
        if(isset($item->unlock_at)){
            $unlock_at= DateTime::createFromFormat(DateTime::ISO8601, $item->unlock_at);
            $quiz->unlock_at = $unlock_at->format('c');
        }
        if(isset($item->published)){$quiz->published = $item->published;}
        if(isset($item->locked_for_user)){$quiz->locked_for_user = $item->locked_for_user;}
        if(isset($item->scoring_policy)){$quiz->scoring_policy = $item->scoring_policy;}
        if(isset($item->allowed_attempts)){$quiz->allowed_attempts = $item->allowed_attempts;}
        
        $quiz->save();
        return $quiz;
    }
    
    public function processSingleQuizQuestion($quizQuestion)
    {
        $question = Quizquestion::firstOrNew(array('question_id' => $quizQuestion->id));
        $question->question_id = $quizQuestion->id;
        $question->quiz_id = $quizQuestion->quiz_id;
        if(isset($quizQuestion->position)){$question->position = $quizQuestion->position;}
        if(isset($quizQuestion->points_possible)){$question->points_possible = $quizQuestion->points_possible;}
        if(isset($quizQuestion->question_name)){$question->name = $quizQuestion->question_name;}
        if(isset($quizQuestion->question_type)){$question->type = $quizQuestion->question_type;}
        if(isset($quizQuestion->question_text)){$question->text = htmlspecialchars(($quizQuestion->question_text));}
        if(isset($quizQuestion->correct_comments)){$question->correct_comments = $quizQuestion->correct_comments;}
        if(isset($quizQuestion->incorrect_comments)){$question->incorrect_comments = $quizQuestion->incorrect_comments;}
        if(isset($quizQuestion->neutral_comments)){$question->neutral_comments = $quizQuestion->neutral_comments;}
        if(isset($quizQuestion->answers)){$question->answers = json_encode($quizQuestion->answers);}
        
        $question->save();
        return $question;
    }
    
    public function saveQuizSubmission($quizSubmission)
    {
        $dbQuizSubmission = QuizSubmission::firstOrNew(
                array('quiz_submission_id' => $quizSubmission->id, 'user_id'=> $quizSubmission->user_id, 'quiz_id'=>$quizSubmission->quiz_id)
            );
        $dbQuizSubmission->quiz_submission_id = $quizSubmission->id;
        $dbQuizSubmission->user_id = $quizSubmission->user_id;
        $dbQuizSubmission->quiz_id = $quizSubmission->quiz_id;
        $dbQuizSubmission->submission_id = $quizSubmission->submission_id;
        $dbQuizSubmission->validation_token = $quizSubmission->validation_token;
        if(isset($quizSubmission->quiz_version)){$dbQuizSubmission->quiz_version = $quizSubmission->quiz_version;}
        if(isset($quizSubmission->attempt)){$dbQuizSubmission->attempt = $quizSubmission->attempt;}
        if(isset($quizSubmission)){$dbQuizSubmission->extra_attempts = $quizSubmission->extra_attempts;}
        if(isset($quizSubmission->attempts_left)){$dbQuizSubmission->attempts_left = $quizSubmission->attempts_left;}
        if(isset($quizSubmission->time_spent)){$dbQuizSubmission->time_spent = $quizSubmission->time_spent;}
        if(isset($quizSubmission->extra_time)){$dbQuizSubmission->extra_time = $quizSubmission->extra_time;}
        if(isset($quizSubmission->started_at)){$dbQuizSubmission->started_at = $quizSubmission->started_at;}
        if(isset($quizSubmission->finished_at)){$dbQuizSubmission->finished_at = $quizSubmission->finished_at;}
        if(isset($quizSubmission->end_at)){$dbQuizSubmission->end_at = $quizSubmission->end_at;}
        if(isset($quizSubmission->workflow_state)){$dbQuizSubmission->workflow_state = $quizSubmission->workflow_state;}
        if(isset($quizSubmission->has_seen_results)){$dbQuizSubmission->has_seen_results = $quizSubmission->has_seen_results;}
        if(isset($quizSubmission->manually_unlocked)){$dbQuizSubmission->manually_unlocked = $quizSubmission->manually_unlocked;}
        if(isset($quizSubmission->overdue_and_needs_submission)){$dbQuizSubmission->overdue_and_needs_submission = $quizSubmission->overdue_and_needs_submission;}
        if(isset($quizSubmission->score)){$dbQuizSubmission->score =  $quizSubmission->score;}
        if(isset($quizSubmission->score_before_regrade)){$dbQuizSubmission->score_before_regrade = $quizSubmission->score_before_regrade;}
        if(isset($quizSubmission->quiz_points_possible)){$dbQuizSubmission->quiz_points_possible = $quizSubmission->quiz_points_possible;}
        if(isset($quizSubmission->kept_score)){$dbQuizSubmission->kept_score = $quizSubmission->kept_score;}
        if(isset($quizSubmission->fudge_points)){$dbQuizSubmission->fudge_points = $quizSubmission->fudge_points;}
        if(isset($quizSubmission->html_url)){$dbQuizSubmission->html_url = $quizSubmission->html_url;}
        
        $dbQuizSubmission->save();
        return $dbQuizSubmission;
    }
    /*
     * SUBMISSIONS
     */
    public function processSubmissionsRequest(SubmissionsRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];
        $userId = $_SESSION['userID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        
        //MULTIPLE ASSIGNMENTS AND POTENTIALLY MULTIPLE USERS
        if($request->getMultipleAssignments())
        {//GET /api/v1/courses/:course_id/students/submissions
            $urlPieces[]="students/submissions";
            
            //STUDENT IDS
            //student_ids can be "All", or a list of actual studentIds
            if($request->getMultipleStudents() && $request->getAllStudents())
            {
                $urlArgs[]="student_ids[]=all";
            }
            else if($request->getMultipleStudents()&&count($request->getStudentIds()>1))
            {
                $ids = json_encode($request->getStudentIds());
                $urlArgs[]="student_ids[]={$ids}";
            }
            else
            {
                $urlArgs[]="student_ids[]={$request->getStudentIds()[0]}";
            }
            
            //ASSIGNMENT IDS
            //assignment_ids can be a list of assignmentIds, or if empty, all assignments will be returned
            
            $assignmentIds = implode(',', $request->getAssignmentIds());
            if(count($request->getAssignmentIds()) > 0)
            {
                $urlArgs[]= "assignment_ids[]={$assignmentIds}";  
            }
                
        }
        //SINGLE ASSIGNMENT, MULTIPLE USERS
        else if($request->getMultipleStudents())
        {   
            // GET /api/v1/courses/:course_id/assignments/:assignment_id/submissions
            $urlPieces[]= "assignments";
            //grab the first assignment id. Shouldn't have more than one (all this has been validated in the SubmissionsRequest constructor)
            $urlPieces[]= $request->getAssignmentIds()[0];
            $urlPieces[]= "submissions";
            
        }
        //SINGLE ASSIGNMENT, SINGLE USER
        else
        {//GET /api/v1/courses/:course_id/assignments/:assignment_id/submissions
            if(($request->getAssignmentIds()))
            {            
                $urlPieces[]= "assignments"; //input1
                $urlPieces[]= $request->getAssignmentIds()[0]; // get the first assignment id from the array (there shouldn't be more than one anyway)
                $urlPieces[] = "submissions";
                $urlPieces[] = $request->getStudentIds()[0];
            }

        }
        if($request->getGrouped())
        {
            $urlArgs[]="grouped=true";
        }
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";
        
        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        
        $response = GuzzleHelper::makeRequest($request, $url);
        
        return $this->processCanvasSubmissionData(json_decode($response->getBody()), $request->getIncludeTags(), $request->getGrouped());
        
    }
    
    /*
     * ASSIGNMENTS
     */
    public function processAssignmentsRequest(AssignmentsRequest $request)
    {//api/v1/courses/:course_id/assignments
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $singleRow = false;
            
        $urlPieces[] = "assignments";
        
        //Attach token
        $urlArgs[]="access_token={$token}";
        $urlArgs[]="per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
//        echo $url;
        $response = GuzzleHelper::makeRequest($request, $url);

        return $this->processCanvasAssignmentData(json_decode($response->getBody()), $courseId, $singleRow);
        
    }
    
    public function processAssignmentGroupsRequest(AssignmentGroupsRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $courseId = $_SESSION['courseID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $singleRow = false;
        $urlPieces[] = "assignment_groups";
        
        $urlArgs[]="include[]=assignments";
        //Attach token
        $urlArgs[]="access_token={$token}";
        $urlArgs[]="per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
        
        $response = GuzzleHelper::makeRequest($request, $url);
        
        return $this->processCanvasAssignmentGroupsData(json_decode($response->getBody()), $courseId, $singleRow);
        
    }
    
    public function getAnalyticsAssignmentData()
    {
        return $this->simpleGet("analytics/assignments");
    }
    public function getAnalyticsStudentAssignmentData($userId=null)
    {//GET /api/v1/courses/:course_id/analytics/users/:student_id/assignments
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        if(is_null($userId))
        {
            $userId = $_SESSION['userID'];
        }
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = 'analytics/users';
        $urlPieces[] = $userId;
        $urlPieces[] = "assignments";
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        $response = GuzzleHelper::getAsset($url);
        return $response->getBody();
    }
    
    public function getUsersInCourse()
    {
    	return $this->simpleGet('users');
    }
    
    public function getStudentsInCourse()
    {
    	return $this->simpleGet('students');
    }
    
    public function getUserEnrollments()
    {///api/v1/users/:user_id/enrollments
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $urlPieces= array();
        $domain = $_SESSION['domain'];
        $userId = $_SESSION['userID'];
        
        $urlPieces[]= "https://{$domain}/api/v1/users/{$userId}/enrollments";
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        
        $response = GuzzleHelper::getAsset($url);
        return $response->getBody();
    } 
        
    public function getGradingStandards()
    {///api/v1/courses/:course_id/grading_standards
        return $this->simpleGet('grading_standards');
    }
    
    public function getCourse()
    {///api/v1/courses/:id
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $userId = $_SESSION['userID'];
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        $response = GuzzleHelper::getAsset($url);
        return json_decode($response->getBody());
    }
    
    public function getAccount($accountId)
    {///api/v1/accounts/:id
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];

        $urlPieces= array();
        //        GET /api/v1/courses/:course_id/files
        $urlPieces[]= "https://{$domain}/api/v1/accounts/{$accountId}";
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        $response = GuzzleHelper::getAsset($url);
        return $response->getBody();
    }
    /*
     * MODULES
     */
    
    private function buildModuleUpdateArgs(UpdatableModule $module)
    {
        $urlArgs = array();
        foreach($module as $key=>$value)
        {
            if(($key === "prerequisite_module_ids") &&($value)&& is_array($value))
            {   
                foreach($value as $prereq)
                {
                    $urlArgs[] = "module[prerequisite_module_ids][]={$prereq}";
                }
                continue;
            }
            if($value)
            {
                $urlArgs[] = "module[{$key}]={$value}";
            }
            
        }
        return $urlArgs;
    }
    
    private function buildModuleItemUpdateArgs(UpdatableModuleItem $moduleItem)
    { 
        $urlArgs = array();
        
        foreach($moduleItem as $key=>$value)
        {
            //cannot update content_id, page_url, or type. (as per Canvas API)
            //The tags will be updated separately since they don't belong to Canvas
            if(($key === "content_id")||($key === "page_url")||$key==="tags"||$key==="type")
            {
                continue;
            }
            if(($key === "completion_requirement_type")&&($value))//make sure value is not null
            {
                $urlArgs[] = "module_item[completion_requirement][type]={$value}";
                continue;
            }
            if(($key === "completion_requirement_min_score")&&($value))//make sure value is not null
            {
                $urlArgs[] = "module_item[completion_requirement][min_score]={$value}";
                continue;
            }
            if($value)//only grab non-null items
            {
                $urlArgs[] = "module_item[{$key}]={$value}";
            }
        }
        return $urlArgs;
    }
    
    private function buildAddModuleArgs(ModulesRequest $request)
    {
        $urlArgs = array();
        $modItem = $request->getModule();
        foreach($modItem as $key => $value) {
            if(($key ==="name")&&(!$value))
            {
                throw new InvalidParameterInRequestObjectException(get_class($request),"name", "Parameter must be a string");
            }
            if(($value) && ($key ==="prerequisite_module_ids") && is_array($value))
            {
                foreach($value as $prereq)
                {
                    $urlArgs[] = "module[prerequisite_module_ids][]={$prereq}";
                }
            }
            else if ($value)
            {
                $urlArgs[] = "module[{$key}]={$value}";
            }
        }
        return $urlArgs;
    }
    
    private function buildAddModuleItemArgs(ModulesRequest $request)
    {
        $urlArgs = array();
        if (!$request->getModuleItem()->title) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"Title", "Parameter is required");
        }

        if (!$request->getModuleItem()->type) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"Type", "Type is required");
        }

        $modItem = $request->getModuleItem();
        foreach($modItem as $key => $value) {
            if(($key==="content_id")&&($value))
            {//Content Id is NOT required for ‘ExternalUrl’, ‘Page’, and ‘SubHeader’ types.
                $type = $request->getModuleItem()->type;
                if(($type==="ExternalUrl")||($type==="Page")||($type==="SubHeader"))
                {
                    continue;
                }
            }
            if(($key==="tags")||($key==="published"))//tags will be handled by us (not by Canvas). Published cannot be set when creating 
            {//a module item
                continue;
            }
            if(($key ==="completion_requirement_type")&&($value))
            {
                $urlArgs[] = "module_item[completion_requirement][type]={$value}";
            }
            else if(($key ==="completion_requirement_min_score")&&($value))
            {
                $urlArgs[] = "module_item[completion_requirement][min_score]={$value}";
            }
            else if ($value)
            {
                $urlArgs[] = "module_item[{$key}]={$value}";
            }
        }
        return $urlArgs;
    }
    
    private function processCanvasModuleData($data, $courseId)
    {   
        $items = array();
        $moduleIdsArray = array();
        $moduleItemIdsArray = array();
        $i = 0;
        $firstItemId = null;
        foreach($data as $moduleRow)
        {
            //assign the first item as the parent IF it's published
            if(is_null($firstItemId) && $moduleRow->published)
            {
                $firstItemId = $moduleRow->id;
            }
             //we'll create an array with all the moduleIds that belong to this courseId
            $moduleIdsArray[] = $moduleRow->id;
            $module = $this->processSingleModule($moduleRow, $courseId, $i, $firstItemId, $moduleItemIdsArray);
            $items[] = $module;
            $i++;
        }

        //since we are updating our DB with fresh Canvas data we MUST check against our DB and make sure we don't have "old" modules stored
        $dbHelper = new DbHelper();
        $dbHelper->qualityAssuranceModules($courseId, $moduleIdsArray);
        $dbHelper->qualityAssuranceModuleItems($courseId, $moduleItemIdsArray);
                
        return $items;
    }
    
    private function processSingleModule($moduleRow, $courseId, $possibleOrder=null, $firstItemId = null, &$itemIdsArr = null)
    {
        //check if module exists
        $module = Module::firstOrNew(array('module_id' => $moduleRow->id));//('moduleId','=',$module->id);
        $module->module_id = $moduleRow->id;
        $module->course_id = $courseId;
        $module->name = $moduleRow->name;
//        $module->position = $moduleRow->position;
        $module->unlock_at = $moduleRow->unlock_at;
        $module->require_sequential_progress = $moduleRow->require_sequential_progress;
        $module->publish_final_grade = $moduleRow->publish_final_grade;
        $module->prerequisite_module_ids = implode(", ",$moduleRow->prerequisite_module_ids);
        $module->items_count = $moduleRow->items_count;
        if(isset($moduleRow->published)){$module->published = $moduleRow->published;}
        if(isset($moduleRow->state)){$module->state = $moduleRow->state;}

        
        if(isset($moduleRow->items)){
            //save moduleItems
            $moduleItems = $this->saveModuleItems($moduleRow->items, $courseId, $itemIdsArr);
            $module->module_items = $moduleItems;
        }
        
        $orderedMod = $this->retrieveOrderedModuleInfo($moduleRow->id, $courseId);
        
        if($orderedMod)
        {
            $module->order = $orderedMod->order;
            $module->parent_id = $orderedMod->parent_id;
        }
        else if(!is_null($firstItemId))
        {
            if($firstItemId==$moduleRow->id)
            {
                $module->parent_id = 1;
            }
            else
            { 
                $module->parent_id = $firstItemId;
            }
        }
        
        $module->save();
        $modArr = $module->toArray();
        $modArr['module_items'] = $module->module_items->toArray();
        return $modArr;
    }
    
    private function retrieveOrderedModuleInfo($moduleId, $courseId)
    {
        $dbHelper = new DbHelper();
        $orderedModule = $dbHelper->getOrderedModuleByModuleId($courseId, $moduleId);
        return $orderedModule;
    }
    
    private function saveModuleItems($moduleItems, $courseId, &$itemIdsArr = null)
    {
        $allItems = array();
        
        foreach($moduleItems as $mItem){
            $itemIdsArr[] = $mItem->id;
            $moduleArr =$this->processSingleModuleItem($courseId, $mItem);
            array_push($allItems, $moduleArr);
        }
        
        return $allItems;
    }
    
    private function processSingleModuleItem($courseId, $mItem)
    {
        $moduleItem = ModuleItem::firstOrNew(array(
            'module_id' => $mItem->module_id,
            'module_item_id'=> $mItem->id
        ));
        $moduleItem->module_item_id = $mItem->id;
        $moduleItem->module_id = $mItem->module_id;
        $moduleItem->course_id = $courseId;
        $moduleItem->position = $mItem->position;
        $moduleItem->title = $mItem->title;
        $moduleItem->indent = $mItem->indent;
        $moduleItem->type = $mItem->type;

        if(isset($mItem->published)){$moduleItem->published = $mItem->published;}


        //if we don't have contentId we'll use the module_item_id. This is for tagging purposes
        $contentId = 0;
        if(isset($mItem->content_id))
        {
            $moduleItem->content_id = $mItem->content_id;
        }
        else
        {
            $moduleItem->content_id = $mItem->id;
        }

        if(isset($mItem->html_url)){$moduleItem->html_url = $mItem->html_url;}
        if(isset($mItem->url)){$moduleItem->url = $mItem->url;}
        if(isset($mItem->page_url)){$moduleItem->page_url = $mItem->page_url;}
        if(isset($mItem->external_url)){$moduleItem->external_url = $mItem->external_url;}
        if(isset($mItem->new_tab)){$moduleItem->new_tab = $mItem->new_tab;}
        if(isset($mItem->completion_requirement)){$moduleItem->completion_requirement = json_encode($mItem->completion_requirement);}
        if(isset($mItem->type))
        {
            $contentDetails;
            if(isset($mItem->content_details))
            {
                $contentDetails = $mItem->content_details;
            }
            else
            {
                $contentDetails = null;
            }
            $content = $this->saveContentDetails($courseId, $mItem->module_id, $mItem->id, $moduleItem->content_id, $mItem->type,$contentDetails);
            $moduleItem->content = $content;
        }

        $moduleItem->save();
        $modArr = $moduleItem->toArray();
        $modArr['content'] = $moduleItem->content->toArray();
        return $moduleItem;
    }
    
    private function saveContentDetails($courseId, $moduleId, $itemId, $contentId, $type, $contentDetails)
    {   
        $content = Content::firstOrNew(array('content_id'=>$contentId));
        $content->content_id= $contentId;
        $content->content_type= $type;
        $content->module_item_id = $itemId;
        //$content->tags=$contentDetails->content_id;
        if(isset($contentDetails->points_possible)){$content->points_possible= $contentDetails->points_possible;}
        if(isset($contentDetails->due_at)){$content->due_at= $contentDetails->due_at;}
        if(isset($contentDetails->unlock_at)){$content->unlock_at= $contentDetails->unlock_at;}
        if(isset($contentDetails->lock_at)){$content->lock_at= $contentDetails->lock_at;}
        if(isset($contentDetails->lock_explanation)){$content->lock_explanation= $contentDetails->lock_explanation;}
                
        $content->save();
        
        return $content;
    }
    
    
    /*
     * ASSIGNMENTS
     */
    private function processCanvasAssignmentData($data, $courseId, $singleRow)
    {
        $assignments= array();
        
        if($singleRow)
        {
            $assignments[] = $this->processSingleAssignment($data);
        }
        else
        {
            foreach($data as $row)
            {
                $assignments[] = $this->processSingleAssignment($row);
            }
        }
        
        return $assignments;
        
    }
    
    private function processSingleAssignment($row)
    {           
        $assignment = Assignment::firstOrNew(array('assignment_id' => $row->id));
        $assignment->assignment_id = $row->id;
        $assignment->assignment_group_id = $row->assignment_group_id;
        $assignment->name = $row->name;
        if(($assignment->description)) {$assignment->description=$row->description;}
        
        if(isset($row->due_at))
        {
            $due_at= DateTime::createFromFormat(DateTime::ISO8601, $row->due_at);
            $assignment->due_at = $due_at->format('c');
        }
        if(isset($row->lock_at))
        {
            $lock_at= DateTime::createFromFormat(DateTime::ISO8601, $row->lock_at);
            $assignment->lock_at = $lock_at->format('c');

        }
        if(isset($row->unlock_at))
        {
            $unlock_at= DateTime::createFromFormat(DateTime::ISO8601, $row->unlock_at);
            $assignment->unlock_at = $unlock_at->format('c');
        }
        if(isset($row->all_dates)){$assignment->all_dates = $row->all_dates;}
        if(isset($row->course_id)){$assignment->course_id = $row->course_id;}
        if(isset($row->html_url)){$assignment->html_url = $row->html_url;}
        if(isset($row->points_possible)){$assignment->points_possible = $row->points_possible;}
        if(isset($row->locked_for_user)){$assignment->locked_for_user = $row->locked_for_user;}
        if(isset($row->quiz_id)){$assignment->quiz_id = $row->quiz_id;}
        if(isset($row->additional_info)){$assignment->additional_info = $row->additional_info;}
        if(isset($row->position)){$assignment->position = $row->position;}
        
        $assignment->save();
        
        return $assignment;
    }
    
    private function processCanvasAssignmentGroupsData($data, $courseId, $singleRow)
    {
        if(($singleRow) || count($data)===1)
        {
            return $this->processSingleAssignmentGroup($data, $courseId);
        }
        else
        {
            $assignmentGroupArray = array();
            foreach($data as $row)
            {
                $assignmentG = $this->processSingleAssignmentGroup($row, $courseId);
                $assignmentGroupArray[] = $assignmentG;
            }
            
            return $assignmentGroupArray;
        }
    }
    
    private function processSingleAssignmentGroup($row, $courseId)
    {
        $assignmentGroup = AssignmentGroup::firstOrNew(array('assignment_group_id' => $row->id));
        $assignmentGroup->assignment_group_id = $row->id;
        $assignmentGroup->name = $row->name;
        $assignmentGroup->position = $row->position;
        $assignmentGroup->course_id = $courseId;
        if(isset($row->rules)){ $assignmentGroup->rules = json_encode($row->rules);}
        $assignmentGroup->group_weight = $row->group_weight;
       
        if(isset($row->assignments))
        {
            $arr = array();
            $assignments = $row->assignments;
            foreach($assignments as $row)
            {
                $assignment = $this->processSingleAssignment($row);
                $arr[] = $assignment;
            }
            $assignmentGroup->assignments = $arr;
            
        }
        
        $assignmentGroup->save();
        
        return $assignmentGroup;
    }
    
    /*
     * SUBMISSIONS
     */
    private function processCanvasSubmissionData($data, $includeTags = false, $grouped = false)
    {
        $submissions = array();
        if($grouped)
        {
            foreach($data as $userSubmissions)
            {
                foreach($userSubmissions->submissions as $submission)
                {
                    $subm = $this->processSingleSubmission($submission, $includeTags);
                    $submissions[] = $subm;
                }
            }
        }
        else
        {
            if(gettype($data)==="array")//we have a single submission
            { //we have multiple submissions
                foreach($data as $row)
                {
                    $subm = $this->processSingleSubmission($row, $includeTags);
                    $submissions[] = $subm;
                }
            }
            else
            {  
                $submissions[] = $this->processSingleSubmission($data, $includeTags);
            }
        }
        return $submissions;
    }
    
    private function processSingleSubmission($row, $includeTags = false)
    {
        $submission = new Submission();
        $submission->submission_id = $row->id;
        $submission->assignment_id = $row->assignment_id;
        if(isset($row->course)){$submission->course = $row->course;}
        if(isset($row->attempt)){$submission->attempt = $row->attempt;}
        if(isset($row->body)){$submission->body = $row->body;}
        if(isset($row->grade)){$submission->grade = $row->grade;}
        if(isset($row->grade_matches_current_submission)){$submission->grade_matches_current_submission = $row->grade_matches_current_submission;}
        if(isset($row->html_url)){$submission->html_url = $row->html_url;}
        if(isset($row->preview_url)){$submission->preview_url = $row->preview_url;}
        if(isset($row->score)){$submission->score = $row->score;}
        if(isset($row->submission_comments)){$submission->submission_comments = $row->submission_comments;}
        if(isset($row->submission_type)){$submission->submission_type = $row->submission_type;}
        if(isset($row->submitted_at)){$submission->submitted_at = $row->submitted_at;}
        if(isset($row->url)){$submission->url = $row->url;}
        if(isset($row->user_id)){$submission->user_id = $row->user_id;}
        if(isset($row->grader_id)){$submission->grader_id = $row->grader_id;}
        if(isset($row->late)){$submission->late = $row->late;}
        if(isset($row->assignment_visible)){$submission->assignment_visible = $row->assignment_visible;}
        
        
        if($includeTags)
        {
            //returns tags
            $tags = $this->matchAssignmentIdWithTags($submission->assignment_id);
            $arr = $submission->toArray();
            $arr['tags'] = $tags;
            return $arr;
        }
        else
        {
            return $submission->toArray();
        }
    }
    
    public function matchAssignmentIdWithTags($assignment_id)
    {
        $assignment = $this->dbHelper->getAssignment($assignment_id);//try to get it from DB
        
        if(is_null($assignment))
        {//get it from Canvas
            $freshData = true;
            $includeTags = true;
            $req = new AssignmentsRequest(ActionType::GET, $assignment_id, $freshData, null, $includeTags);
            $this->processAssignmentsRequest($req);
            
            //try to get the assignment again now that we got the data from Canvas. 
            //If it fails, then return the submission with an empty array for tags
            $assignment = $this->dbHelper->getAssignment($assignment_id);//try to get it from DB again
            if(is_null($assignment))
            {
                return [];
            }
            else
            {
                $assignmentWithTags = $this->dbHelper->matchAssignmentWithTags($assignment);
                return $assignmentWithTags['tags'];
            }
        }
        else
        {
            $assignmentWithTags = $this->dbHelper->matchAssignmentWithTags($assignment);
            return $assignmentWithTags['tags'];
        }   
    }
    
    /*
     * private utility functions
     */
    
    private function initUrl()
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $courseId = $_SESSION['courseID'];

        $urlPieces= array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";
        return $urlPieces;
    }
    
    private function simpleGet($canvasItem)
    {
        $urlPieces= $this->initUrl();
        $token = \Crypt::decrypt($_SESSION['userToken']);
        $urlArgs = array();
        $urlPieces[] = $canvasItem;
        
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        
        $response = GuzzleHelper::getAsset($url);
        return $response->getBody();
    }
}