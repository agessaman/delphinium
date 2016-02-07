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

			$req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleAssignments, $includeTags, $includeTags, $grouped);

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
}
