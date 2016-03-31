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
            'instance'	=> [
                'title'             => 'Configuration:',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }
	
	public function onRun()
    {
		try
        {
        /*Notes:
            is an instance set? yes show it
            else get all instances
                is there an instance with this course? yes use it
            else create dynamicInstance, save new instance, show it
        */
            if (!isset($_SESSION)) { session_start(); }
            $courseID = $_SESSION['courseID'];
            $name = $this->alias .'_'. $_SESSION['courseID'];
            
            // if instance has been set
            if( $this->property('instance') )
            {
                //instance set in CMS getInstanceOptions()
                $config = CompetenceModel::find($this->property('instance'));
                //add $course->id to $config for form field

            } else {
                // look for instances created for this course
				$instances = CompetenceModel::where('name','=', $name)->get();
				
				if(count($instances) === 0) { 
					// no record found so create a new dynamic instance
					$config = new CompetenceModel;// db record
					$config->Name = $name;
					$config->Size = 'Medium';//$config->size = '20%';
                    $config->Color = '#4d7123';//uvu green
                    $config->Animate = '1';//true
					$config->save();// save the new record
				} else {
					//use the first record matching course
					$config = $instances[0];
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

			/*get Assignments & Submissions ***** & enrolled students?
				submissions data is only available if viewed by a Learner
				Module Items data is used if Instructor
                
                if instructor, you can configure the component
                and view the tags set in Stem that define competencies
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
                // Use the primary key of the record you want to update
                $this->page['recordId'] = $config->id;
                // Append Instructions page
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

                $studentIds = array($_SESSION['userID']);
                $allStudents = false;
                $allAssignments = true;
                $multipleStudents = false;
                $multipleAssignments = true;
                $includeTags = true;
                $grouped = true;

                $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments, $includeTags, $grouped);

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
		$instances = CompetenceModel::all();
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
    
    * id is disabled in fields.yaml
    * id & course are also hidden
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
		$config->save();// update original record 
		return json_encode($config);
    }
    
}
