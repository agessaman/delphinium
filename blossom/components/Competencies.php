<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Competencies as CompetenceModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// score

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
            'instance'	=> [
                'title'             => 'Competencies Configuration',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
            ]
        ];
    }
	
    public function onStart()
    {
        /* COURSE & INSTANCE FAIL IF HERE use onRender
            get course_id component is in (DEV)
            get instance (with course ID)???

            if (no instance with this course ID){
                create instance with courseID
            }
            launch instance with course ID
        */ 
    }
    public function onRender()
    {
		//getInstanceOptions()
		$config = CompetenceModel::find($this->property('instance'));
        //Name is just for instances drop down. Use in component display?
		
        $roots = new Roots();
        $course = $roots->getCourse();
		//$this->page['course'] = json_encode($course);
		
		//add $course->id to $config dynamic for form field
		$config->course_id = $course->id;
		$this->page['config'] = json_encode($config);
		
		// comma delimited string ?
        if (!isset($_SESSION)) { session_start(); }
        $roleStr = $_SESSION['roles'];
        $this->page['role'] = $roleStr;
    }

    public function onRun()
    {
		try
        {
            $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/competencies.css");//overide alert !important

			/*get Assignments & Submissions ***** & enrolled students?
				live data is only available if viewed by a Learner
				fake data is used if Instructor
                
                if instructor, add configure component
				todo: Instructor can choose a student to view their progress?
                todo: Instructor can configure Stem from here?
			**************************************************/
			$roots = new Roots();
			//PHP: if($_SESSION['roles'].indexOf('Instructor'))
			if($_SESSION['roles'] == 'Instructor')
			{
				//https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
				$this->addCss('/plugins/delphinium/blossom/formwidgets/colorpicker/assets/vendor/colpick/css/colpick.css', 'delphinium.blossom');
				$this->addJs('/plugins/delphinium/blossom/formwidgets/colorpicker/assets/vendor/colpick/js/colpick.js', 'delphinium.blossom');
				$this->addCss('/plugins/delphinium/blossom/formwidgets/colorpicker/assets/css/colorpicker.css', 'delphinium.blossom');
				$this->addJs('/plugins/delphinium/blossom/formwidgets/colorpicker/assets/js/colorpicker.js', 'delphinium.blossom');
				
				// Build a back-end form with the context of 'frontend'
				$formController = new \Delphinium\Blossom\Controllers\Competencies();
				$formController->create('frontend');
				
				// Append the formController to the page
				$this->page['form'] = $formController;
				//form items should match $config->Name, color, animate, id, course
				//Competencies[Size]
			}
			if($_SESSION['roles'] == 'Learner')
			{
				$req = new AssignmentsRequest(ActionType::GET);
				$res = $roots->assignments($req);

				$assignmentIds = array();// for submissionsRequest
				$assignments = array();// for points_possible // REPLACE
				foreach ($res as $assignment) {
					array_push($assignmentIds, $assignment["assignment_id"]);
					array_push($assignments, $assignment);
				}
				
				$this->page['assignments']=json_encode($assignments);
				
				/* todo:
					instructor chooses an enrolled student from a dropdown
					to see how that students competencies?
				*/
				$studentIds = null;//['1604486'];//Test Student
				$allStudents = true;
				$allAssignments = true;
				$multipleStudents = false;
				$multipleAssignments = true;
				$includeTags = true;
				$grouped = true;

				$req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleAssignments, $includeTags, $includeTags, $grouped);

				$submissions = $roots->submissions($req);
				$this->page['submissions']=json_encode($submissions);// score
            }
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

    public function getInstanceOptions()
    {
        /*https://octobercms.com/docs/plugin/components#dropdown-properties
		*  The method should have a name in the following format: get*Property*Options()
		*  where Property is the property name
		*/
		$instances = CompetenceModel::where("Name","!=","")->get();
        $array_dropdown = ['0'=>'- select Instance - '];//text in dropdown

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }
        return $array_dropdown;
    }
	
    /**
	* update, add course_id
	* save to database and return updated
	*/
	public function onSave()
    {
		$config = CompetenceModel::find($this->property('instance'));
		$data = post('Competencies');
		
		$config->Name = $data['Name'];
		$config->Size = $data['Size'];
		$config->Color = $data['Color'];
		$config->Animate = $data['Animate'];
		$config->course_id = $data['course_id'];
		$config->save();// update original record 

		return json_encode($config);
    }
    
	public function onUpdate() {
		return ['message' => 'Updated ...'];
	}
	// test: for controller.formExtendFields
	public function getConfig()
    {
		$config = CompetenceModel::find($this->property('instance'));
        return $config;
	}
}
