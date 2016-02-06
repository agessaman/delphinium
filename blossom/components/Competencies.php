<?php namespace Delphinium\Blossom\Components;

use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Models\ModuleItem as DbModuleItem;
use Delphinium\Roots\Models\Quizquestion;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Utils;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Enums\ModuleItemType;
use Delphinium\Roots\Enums\CompletionRequirementType;
use Delphinium\Roots\DB\DbHelper;
use Delphinium\Roots\Lmsclasses\CanvasHelper;
use Cms\Classes\ComponentBase;
use \DateTime;
use \DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostFile;
use Delphinium\Iris\Components\Iris;
use Cms\Classes\ComponentManager;
use \Delphinium\Blade\Classes\Rules\RuleBuilder;
use \Delphinium\Blade\Classes\Rules\RuleGroup;
use Delphinium\Roots\Guzzle\GuzzleHelper;
use Delphinium\Blossom\Components\Grade;
use Delphinium\Blossom\Components\Experience;

/* additional from Stem Manager */
use Delphinium\Stem\Classes\ManagerHelper as IrisClass;
use Delphinium\Roots\Enums\Lms;

class Competencies extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Competencies',
            'description' => 'Shows students completion of core Competencies'
        ];
    }

    public function defineProperties()
    {
        return [
            'Competencies' => [
                'title'        => 'Number of Competencies',
                'description'  => 'Enter number of Competencies',
                'type'         => 'string',
                'default'      => '3',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The number of Competencies is required and should be integer.'
            ],

            'Animate' => [
                'title'        => 'Animate',
                'type'         => 'dropdown',
                'default'      => 'true',
                'options'      => ['true'=>'True', 'false'=>'False']
            ],

            'Size' => [
                'title'        => 'Size',
                'type'         => 'dropdown',
                'default'      => 'medium',
                'options'      => ['small'=>'Small', 'medium'=>'Medium', 'large'=>'Large']
            ]
        ];
    }

    public function onRender()
    {
        $this->page['competencies'] = $this->property('Competencies');
        $this->page['competenciesAnimate'] = $this->property('Animate');
        $this->page['competenciesSize'] = $this->property('Size');// err Medium is not defined
    }

    public function onRun()
    {
        try
        {
			$this->addJs("/plugins/delphinium/blossom/assets/javascript/jquery.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/competencies.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
            
            //$freshData = false;
            //$this->prepareData($freshData);// get modules //UNUSED

			/*get Assignments & submissions ******************
				data N/A if DevConfig Instructor  MUST BE Student
				add: if instructor chooses a student?
				EX:SubmissionsRequest($actionType, array $studentIds = null, $allStudents = false, array $assignmentIds = array(),
				$allAssignments = false, $multipleStudents = false, $multipleAssignments = false, $includeTags = false, $grouped = false)
			**************************************************/
			$roots = new Roots();
			$req = new AssignmentsRequest(ActionType::GET);
			$res = $roots->assignments($req);

			$assignmentIds = array();// for submissions
			$assignments = array();// for points_possible
			foreach ($res as $assignment) {
				array_push($assignmentIds, $assignment["assignment_id"]);
				array_push($assignments, $assignment);
			}
			//$this->page['assignmentIds']=json_encode($assignmentIds);//UNUSED
			$this->page['assignments']=json_encode($assignments);
			
			$studentIds = ['1604486'];//null;//['1505562'];//Test Student
			$allStudents = true;
				//$assignmentIds = array();//above
			$allAssignments = true;
			$multipleStudents = false;
			$multipleAssignments = true;
			$includeTags = true;
			$grouped = true;
			
			$req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, 
										  $allAssignments, $multipleAssignments, $includeTags, $includeTags, $grouped);

			$res = $roots->submissions($req);
			$this->page['submissions']=json_encode($res);
        }

        catch (\GuzzleHttp\Exception\ClientException $e) {
            return;
        }
        catch(Delphinium\Roots\Exceptions\NonLtiException $e)
        {
            if($e->getCode()==584)
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
        }
        catch(\Exception $e)
        {
            if($e->getMessage()=='Invalid LMS')
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
            return \Response::make($this->controller->run('error'), 500);
        }
    }

// ********** UNUSED ************************
    //from Stem Manager Get Modules
    public function prepareData($freshData)
    {
        $roots = new Roots();
        $tempArray = $this->getModules($freshData);

        $this->page['moduleData'] = json_encode($tempArray);
        $tags = $roots->getAvailableTags();
        if(strlen($tags)>0)
        {
            $tags = explode(', ', $tags);
        }
        else
        {
            $tags = [];
        }
        $this->page['avTags'] = json_encode($tags);

        $completionReqs = $roots->getCompletionRequirementTypes();
        $result = array();
        $i=0;
        foreach($completionReqs as $type)
        {
            $item = new \stdClass();

            $item->id = $i;
            $item->value=$type;
            $item->text = $this->getText($type);
            $result[] = $item;

            $i++;
        }
        $this->page['completionRequirementTypes']= json_encode($result);
        
// using: data N/A if DevConfig Instructor 
// submissions(SubmissionsRequest $request) ******************
        /*
        $req = new AssignmentsRequest(ActionType::GET);

        $res = $roots->assignments($req);

        $assignmentIds = array();
        $assignments = array();
        foreach ($res as $assignment) {
            array_push($assignmentIds, $assignment["assignment_id"]);
            array_push($assignments, $assignment);
        }
        //$this->page['assignmentIds']=json_encode($assignmentIds);
        $this->page['assignments']=json_encode($assignments);
        
        $studentIds = ['1604486'];//null;//['1505562'];//
        $allStudents = true;
        $allAssignments = true;
        $multipleStudents = false;
        $multipleAssignments = true;
        $includeTags = true;
        $grouped = true;
        
//SubmissionsRequest($actionType, array $studentIds = null, $allStudents = false, array $assignmentIds = array(), $allAssignments = false, 
//$multipleStudents = false, $multipleAssignments = false, $includeTags = false, $grouped = false)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, 
                                      $allAssignments, $multipleAssignments, $includeTags, $includeTags, $grouped);

        $res = $roots->submissions($req);
      
        $this->page['submissions']=json_encode($res);
        
        */
// Must be Instructor to return data        
//getAnalyticsStudentAssignmentData($userId=null)
//GET /api/v1/courses/:course_id/analytics/users/:student_id/assignments

//Breaks if Student: cant find ["submission"]["score"]
       /* 
        $includeTags = true;// returns ONLY if DevConfig Instructor
        $res = $roots->getAnalyticsAssignmentData($includeTags);
        
        $userId = '1604486';
        $helper = new CanvasHelper();
        $res = $helper->getAnalyticsAssignmentData($userId);
    
        $this->page['analytics']=json_encode($res);
       */ 
    }
    
    public function getModules($freshData)
    {
        $moduleId = null;// From courseId ?
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

        $simpleModules = array();
        foreach($modArr as $item)
        {
            $mod = new \stdClass();

            $mod->id = $item['module_id'];
            $mod->value=$item['name'];
            $simpleModules[] = $mod;
        }
        $this->page['rawData'] = json_encode($simpleModules);

        $iris = new IrisClass();
        $result = $iris->buildTree($modArr);

        $tempArray =array();

        if(count($result)<1) //there weren't any parent-child relationships
        {
            $parent;
            $allChildren;
            $final = array();

            //The parent will be the first PUBLISHED item
            $firstItem;
            foreach($moduleData as $item)
            {
                if($item['published'] == "1")
                {
                    $firstItem = $item;
                    break;
                }
            }

            $newArr = $this->unsetValue($modArr, $firstItem);//remove parent from array
            $firstParentId=$firstItem["module_id"];
            $i=0;
            foreach($newArr as $item)
            {
                $item["parent_id"] = $firstParentId;
                //each item must have a parentId of the first module
                $item["children"] = [];
                $item["order"] = $i;
                $final[] = $item;
                $i++;
            }

            //remove the first Item (which is the parent)
            $firstItem["parent_id"] = 1;
            $firstItem["children"]=$final;
            $firstItem["order"]=0;

            $tempArray[] = $firstItem;
        }
        else
        {
            $tempArray = $result;
        }
        return $tempArray;
    }
    
    
    private function getText($type)
    {
        switch($type)
        {
            case 'must_view':
                return "view the item";
            case 'must_contribute':
                return 'contribute';
            case 'must_submit':
                return "score at least";
        }
    }
}
/* // the original code inside try{:
            $this->roots = new Roots();
            $req = new AssignmentsRequest(ActionType::GET);

            $res = $this->roots->assignments($req);
        
            $assignments = array();
            foreach ($res as $assignment) {
                $assignment_array = array('assignment_id' => $assignment["assignment_id"],
                    'quiz_id' => $assignment["quiz_id"],
                    'tags' => "");
                array_push($assignments, $assignment_array);
                echo $assignment["assignment_id"]." : ";
            }
        
        
            $moduleId = null;
            $moduleItemId = null;
            $includeContentDetails = true;
            $includeContentItems = true;
            $module = null;
            $moduleItem = null;
            
            $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems,
                $includeContentDetails, $module, $moduleItem , $freshData) ;

            $res = $this->roots->modules($req);
//echo $res;
//$mod=$res[0];
//echo $mod;
//echo "<hr/>";


            $tags = array();
            foreach ($res as $module) {
 //TJ: Invalid argument supplied for foreach ($module->relations as $items) {
// /Library/WebServer/Documents/delphinium/plugins/delphinium/blossom/components/Competencies.php  
    
                //foreach ($module->relations as $items) {
                foreach ($module->module_items as $items) {
               // echo $items;
                
                    //foreach ($items as $item) {
                    //echo $item;//->content;// neither
                    
                        //foreach ($item->relations as $contents) {
                        foreach ($items->content as $content) {
                            //foreach ($contents as $content) {
                                $tag_array = array('content_id' => $content->attributes["content_id"],
                                    'tags' => $content->attributes["tags"],);
                                array_push($tags, $tag_array);
                            echo $content->attributes["tags"]." : ";//compentency tag?
                            //}
                        }
                    
                   // }
                }
            }

        //echo $tags;
        
            foreach ($assignments as $i => $assignment) {
                foreach ($tags as $tag) {
                    if($assignment["quiz_id"]==$tag["content_id"]){
                        $assignment["tags"] = $tag["tags"];
                        $assignments[$i]= $assignment;
                    }
                }
            }
*/