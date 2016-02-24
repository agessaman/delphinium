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

            'copy_id' => [
                'title'        => 'Copy Name:',
                'type'         => 'string'
            ],
            'instance'	=> [
                'title'             => 'Configuration:',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }
	
    /* options?
    'Size' => [
                'title'        => 'Size',
                'type'         => 'dropdown',
                'default'      => 'Medium',
                'options'      => ['Small'=>'Small', 'Medium'=>'Medium', 'Large'=>'Large']
            ]
    */
    //The CMS controller executes this method before the default markup is rendered.
    public function onRender()
    {
    /*
        is an insance set? yes show it
        
        else is copy set? 
            -yes check if there is an instance that matches copy + course show it
        
        else create dynamicInstance, save new instance show it
    */
        if (!isset($_SESSION)) { session_start(); }
    
        $courseID = $_SESSION['courseID'];
		// if instance
        if( $this->property('instance') )
        {
            //instance set in CMS getInstanceOptions()
            $config = CompetenceModel::find($this->property('instance'));
            //add $course->id to $config for form field
            $config->course_id = $_SESSION['courseID'];//$course->id;
            //$config->save();//??? update original record now ???
            
        } else {
            // if copy has a name 
            $copyLength = strlen($this->property('copy_id'));
            if($copyLength > 0 )
            {
                // find all matching course
                $instances = CompetenceModel::table()->where('course_id',$courseID);
                // find instance with copy
                foreach ($instances as $instance)
                {
                   if($instance->copy_id == $this->property('copy_id') )
                   {
                       $config = $instance;
                       break;// got first found
                   }
                }
                // no match found so create one
                if(!$config) { 
                    $copyLength = 0;
                }
            }
            
            if($copyLength == 0 )
            {
                //$config = dynamicInstance();// undefined use onRun?
                $config = new CompetenceModel;// db record
                $config->Name = 'dynamic_';//+ total records count?
                $config->Size = 'Medium';
                $config->Color = '#4d7123';//uvu green
                $config->Animate = '1';//true
                $config->course_id = $_SESSION['courseID'];// or null
                $config->copy_id = '';
                $config->save();// create new record
                //$this->property('instance')->selected[$config.id];// Error: Can't use method return value in write context
                // need to set for onSave
                // this would be the selected dropdown item #
                //$this->property('instance')->options[$config.id];
                //$this->property('instance')->default=$config.id;
                //https://octobercms.com/forum/post/how-to-pass-variable-to-component-by-overriding-property
            }
        }
        
		$this->page['config'] = json_encode($config);
		$this->page['instance'] = $this->property('instance');// instance id#
		// comma delimited string ?
        //if (!isset($_SESSION)) { session_start(); }
        $roleStr = $_SESSION['roles'];
        
        if(stristr($roleStr, 'Learner')) {
            $roleStr = 'Learner';
        } else { 
            $roleStr = 'Instructor';
        }
        $this->page['role'] = $roleStr;// only one or the other
    }
    
    public function onRun()
    {
		try
        {
			/*get Assignments & Submissions ***** & enrolled students?
				live data is only available if viewed by a Learner
				fake data is used if Instructor
                
                if instructor, add configure component
				todo: Instructor can choose a student to view their progress?
                todo: Instructor can configure Stem from here?
			**************************************************/
            $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/competencies.css");//overide alert !important
			$roots = new Roots();
            $roleStr = $_SESSION['roles'];
            if(stristr($roleStr, 'Instructor'))
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
            //if($_SESSION['roles'] == 'Learner')
            if(stristr($roleStr, 'Learner'))
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
        * Fill the Competencies Configuration [dropdown] in CMS
		*/
		$instances = CompetenceModel::all();//where("Name","!=","")->get();
        $array_dropdown = ['0'=>'- select Instance - '];//id, text in dropdown

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }
        return $array_dropdown;
    }
	
    /**
	* update, add course_id
	* save to database and return updated
    
    if id is disabled in fields.yaml
    $data does not contain .id
    if $config = new CompetenceModel sql Error at save()
    
    if new dynamic instance, $config.id is unknown
    Need to set: $this->property('instance') to update
	*/
	public function onSave()
    {
		$config = CompetenceModel::find($this->property('instance'));
		$data = post('Competencies');
        //echo json_encode($data));
        //$config = new CompetenceModel;//new \stdClass;//new stdClass();// = new class{};//(object)[];// = new \stdClass;// initialize first
		//$config->id = intval($data['id']);// id is now in $data :error: Creating default object from empty value
		$config->Name = $data['Name'];
		$config->Size = $data['Size'];
		$config->Color = $data['Color'];
		$config->Animate = $data['Animate'];
		$config->course_id = $data['course_id'];//hidden
        $config->copy_id = $data['copy_id'];//hidden figure out how to update
		$config->save();// update original record 

		return json_encode($config);
        
    }
    
	// test: for controller.formExtendFields
	public function getConfig()
    {
		$config = CompetenceModel::find($this->property('instance'));
        return $config;
	}
    
    //onRender() undefined
    public function dynamicInstance()
    {
        $config = new CompetenceModel;// db record
        $config->Name = 'New Instance';//+ total records count?
        $config->Size = 'Medium';
        $config->Color = '#4d7123';//uvu green
        $config->Animate = '1';//true
        $config->course_id = $_SESSION['courseID'];// or null
        $config->copy_id = '';
        $config->save();// create new record
        return $config;
    }
}
