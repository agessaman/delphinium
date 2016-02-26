<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Competencies as CompetenceModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\ModulesRequest;//for locked & Instructor view
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
                'type'         => 'string',
                'default'      => 'copy-1',
                'required'     => 'true',
                'validationPattern' => '^(?!\s*$).+',
                'validationMessage' => 'This field cannot be left blank.'
            ],
            'instance'	=> [
                'title'             => 'Configuration:',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }
	/*  copy id must have a string 
        validation above should warn user
        also in backend New form!!!
    */
    //The CMS controller executes this method before the default markup is rendered.
    public function onRender()
    {
    /*
        is an insance set? yes show it
        
        else get all instances
            is copy set?
            -yes check for an instance that matches copy + course show it
            
            is there an instance with this course? yes use it
        else create dynamicInstance, save new instance show it
    */
        if (!isset($_SESSION)) { session_start(); }
    
        $courseID = $_SESSION['courseID'];
		// if instance has been set
        if( $this->property('instance') )
        {
            //instance set in CMS getInstanceOptions()
            $config = CompetenceModel::find($this->property('instance'));
            //add $course->id to $config for form field
            $config->course_id = $_SESSION['courseID'];//$course->id;
            $config->save();//update original record now in case it did not have course
            
        } else {
            // if copy has a name 
            $copyLength = strlen($this->property('copy_id'));
            if($copyLength > 0 )
            {
                // find all matching course 
                $instances = CompetenceModel::where('course_id','=', $courseID)->get();
                $instCount = count($instances);
                if($instCount == 0) { 
                    $copyLength = 0;// none found
                } else {
                    // find instance with copy
                    $flag=false;
                    foreach ($instances as $instance)
                    {
                       if($instance->copy_id == $this->property('copy_id') )
                       {
                           $config = $instance;
                           $flag=true;
                           break;// got first found
                       }
                    }
                    
                    //yes found courses but not matching copy. use the first one found with course id
                    if( !$flag ) { $config = $instances[0]; }
                }
            }
            // no match found so create new one
            if($copyLength == 0 )
            {
                //$config = dynamicInstance();// undefined use onRun?
                $config = new CompetenceModel;// db record
                $config->Name = 'dynamic_';//+ total records count?
                $config->Size = 'Medium';
                $config->Color = '#4d7123';//uvu green
                $config->Animate = '1';//true
                $config->course_id = $_SESSION['courseID'];// or null
                $config->copy_id = $this->property('copy_id');//
                $config->save();// create new record
                ///$this->property('instance', $config->id);// instance=id#
                //dont need to set for onSave
            }
        }
        
		$this->page['config'] = json_encode($config);
		// comma delimited string ?
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
                
                // Instructions page
                $instructions = $formController->makePartial('instructions');
                $this->page['instructions'] = $instructions;
                
                //simplified modules data *** testing if better than assignments includes locked
                $freshData = false;

                $moduleId = null;
                $moduleItemId = null;
                $includeContentDetails = true;
                $includeContentItems = true;
                $module = null;
                $moduleItem = null;

                $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems,
                    $includeContentDetails, $module, $moduleItem , $freshData);

                $moduleData = $roots->modules($req);
                $modArr = $moduleData->toArray();

                $simpleModules = array();
                foreach($modArr as $item)
                {
                    $mod = new \stdClass();

                    $mod->id = $item['module_id'];
                    $mod->title=$item['name'];
                    $mod->locked=$item['locked'];
                    $mod->items =$item['module_items'];//REPLACES assignments

                    // items contain:
                    //module_items[i].content[0].title & .url, maybe .type
                    //module_items[i].content[0].points_possible & .tags

                    $simpleModules[] = $mod;
                }
                $this->page['modules'] = json_encode($simpleModules);
                ///$this->page['modata'] = json_encode($moduleData);// complete array remove when done
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
	* update, add course_id & copy_id
	* save to database and return updated
    
    * id is disabled in fields.yaml
    * id, course & copy are also hidden
    * $data gets .id from config setting hidden field
    * called from instructorView configure settings
	*/
	public function onUpdate()
    {
        $data = post('Competencies');
        $did = intval($data['id']);
        $config = CompetenceModel::find($did);
        //echo json_encode($config);
		$config->Name = $data['Name'];
		$config->Size = $data['Size'];
		$config->Color = $data['Color'];
		$config->Animate = $data['Animate'];
		$config->course_id = $data['course_id'];//hidden
        $config->copy_id = $data['copy_id'];//hidden
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
