<?php namespace Delphinium\BirdoParadise\Components;

use Cms\Classes\ComponentBase;
use Delphinium\BirdoParadise\Models\Modulemap as ModulemapModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// student progress
//parent/children
use Delphinium\BirdoParadise\Classes\ManagerHelper as IrisClass;

class Modulemap extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'modulemap Component',
            'description' => 'No description provided yet...'
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
    
    public function onRun()
    {
        try
        {
            /*
            is an instance set? yes show it

            else get all instances
                is copy set?
                -yes check for an instance that matches copy + course show it

                is there an instance with this course? yes use it
            else create dynamicInstance, save new instance show it
            
            Dont forget have the component set up from Here:
            https://github.com/ProjectDelphinium/delphinium/wiki/3.-Setting-up-a-Project-Delphinium-Dev-environment-on-localhost
            */
            if (!isset($_SESSION)) { session_start(); }

            $courseID = $_SESSION['courseID'];
            // if instance has been set
            if( $this->property('instance') )
            {
                //instance set in CMS getInstanceOptions()
                $config = ModulemapModel::find($this->property('instance'));
                $config->course_id = $_SESSION['courseID'];//$course->id;
                $config->save();//update original record now in case it did not have course

            } else {
                // if copy has a name 
                $copyLength = strlen($this->property('copy_id'));
                if($copyLength > 0 )
                {
                    // find all matching course 
                    $instances = ModulemapModel::where('course_id','=', $courseID)->get();
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
                               break;// got first one found
                           }
                        }
                        //yes found courses but not matching copy. use the first one found with course id
                        if( !$flag ) { $config = $instances[0]; }
                    }
                }
                // no match found so create new one
                if($copyLength == 0 )
                {
                    $config = new ModulemapModel;// db record
                    $config->name = 'dynamic_';//+ total records count?
                    $config->modules = '';
                    // add your fields
                    $config->course_id = $_SESSION['courseID'];// or null
                    $config->copy_id = $this->property('copy_id');
                    $config->save();// create a new record
                }
            }

            $this->page['config'] = json_encode($config);
            // comma delimited string
            $roleStr = $_SESSION['roles'];

            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
            $this->page['role'] = $roleStr;// only one or the other
            
        /*    // include any css or javascript here: loaded in default.htm
            $this->addCss("/plugins/delphinium/birdoparadise/assets/css/bootstrap.min.css");
            $this->addCss("/plugins/delphinium/birdoparadise/assets/css/bop.css");
			$this->addJs("/plugins/delphinium/birdoparadise/assets/javascript/jquery.min.js");
            $this->addJs("/plugins/delphinium/birdoparadise/assets/javascript/bootstrap.min.js");
        */
            // include the backend form with instructions
            if($roleStr == 'Instructor')
			{
				//https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
				// Build a back-end form with the context of 'frontend'
				$formController = new \Delphinium\BirdoParadise\Controllers\Modulemap();
				$formController->create('frontend');
				
				// Append the formController to the page
				$this->page['form'] = $formController;
                
                // Instructions page
                $instructions = $formController->makePartial('instructions');
                $this->page['instructions'] = $instructions;
                
                //other code specific to instructor view goes here
            }
            if($roleStr == 'Learner')
			{
                //code specific to the student view goes here
				// assignments & submissions
                $this->getStudentSubmissions();
            }
			// for both 
			$moduledata = $this->getModules();
			$this->page['moduledata'] = json_encode($moduledata);
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
        * Fills the Configuration [dropdown] in CMS 
		*/
		$instances = ModulemapModel::all();
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
    
    * id can be disabled in fields.yaml
    * id, course & copy can also be hidden
    * $data gets .id from config setting field
    * called from instructor view configure settings
	*/
	public function onUpdate()
    {
        $data = post('Modulemap');
        $did = intval($data['id']);
        $config = ModulemapModel::find($did);
        //echo json_encode($config);
		$config->name = $data['name'];
		$config->units = $data['units'];
        $config->modules = $data['modules'];

		$config->course_id = $data['course_id'];//hidden
        $config->copy_id = $data['copy_id'];//hidden
		$config->save();// update original record 
		return json_encode($config);// back to instructor
    }
    
    public function getModules()
    {
        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = true;
        $includeContentItems = true;
        $module = null;
        $moduleItem = null;
        $freshData = false;

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
        //$this->page['rawData'] = json_encode($simpleModules);
        $iris = new IrisClass();// reorder for parent/children
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

    private function unsetValue(array $array, $value, $strict = TRUE)
    {
        if(($key = array_search($value, $array, $strict)) !== FALSE) {
            unset($array[$key]);
        }
        return $array;
    }

	public function getStudentSubmissions()
	{
		$req = new AssignmentsRequest(ActionType::GET);
		$res = $roots->assignments($req);

		$assignmentIds = array();// for submissionsRequest
		$assignments = array();// for points_possible
		foreach ($res as $assignment) {
			array_push($assignmentIds, $assignment["assignment_id"]);
			array_push($assignments, $assignment);
		}
		
		$this->page['assignments']=json_encode($assignments);
		
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
	
/* End of class */
}