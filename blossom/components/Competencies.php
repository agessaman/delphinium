<?php namespace Delphinium\Blossom\Components;

use Delphinium\Blossom\Models\Competencies as CompetenceModel;
use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\ModulesRequest;//for locked
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// score


class Competencies extends ComponentBase
{
/*	public $Color;// hex string #FF00FF
	public $Animate;// bool switch
	public $Size;// string
	
	function setColor($Color) {
        $this->Color = $Color;
    }
	function setAnimate($Animate) {
        $this->Animate = $Animate;
    }
	function setSize($Size) {
        $this->Size = $Size;
    }
	function getColor() {
        return $this->Color;
    }
	function getAnimate() {
        return $this->Animate;
    }
	function getSize() {
        return $this->Size;
    }
*/
    public function componentDetails()
    {
        return [
            'name'        => 'Competencies',
            'description' => 'Shows students completion of core Competencies'
        ];
    }

    public function onRender()
    {
		// moved to onRun
    }

    public function onRun()
    {
		$config = CompetenceModel::find($this->property('instance'));
		
        $this->page['competenciesColor'] = $config->Color;//Main Color for Amount
        $this->page['competenciesAnimate'] = $config->Animate;
        $this->page['competenciesSize'] = $config->Size;
		
        try
        {
            $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/competencies.css");//overide alert css !important
            //echo '<div id="loader" class="container spinner"></div>';//preloader USELESS
            
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/jquery.min.js");// before BS.js
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/bootstrap.min.js");
			$this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/competencies.js");
			
			/*get Modules, Assignments & Submissions ******************
				data is N/A if DevConfig Instructor  MUST BE for a Student
				add: Instructor can choose a student?
             ___   
            if this part is in a function unused data gets handled by garbage collection
            only need trimmed $simpleModules and submissions! 
            $assignments and parts of $moduleData become unused
			**************************************************/
			$roots = new Roots();
			$req = new AssignmentsRequest(ActionType::GET);
			$res = $roots->assignments($req);

			$assignmentIds = array();// for submissionsRequest
			$assignments = array();// for points_possible // REPLACE
			foreach ($res as $assignment) {
				array_push($assignmentIds, $assignment["assignment_id"]);
				array_push($assignments, $assignment);
			}
            // Replace with modules data ???
			$this->page['assignments']=json_encode($assignments);// REPLACE

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
            
        //simplify modules data *** testing if better than assignments includes locked
			$freshData = false;
			//$simpleModules = $this->getModules($freshData);
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
                $mod->value=$item['name'];
                $mod->locked=$item['locked'];
                $mod->items =$item['module_items'];//REPLACES assignments
                
                // I need:
                //module_items[i].content[0].title & .url, maybe .type
                //module_items[i].content[0].points_possible & .tags

                $simpleModules[] = $mod;
            }
            //title, url? , tags, points_possible
			$this->page['modules'] = json_encode($simpleModules);
            ///$this->page['modata'] = json_encode($moduleData);// complete array remove when done
        
            
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

    public function getInstanceOptions()
    {
        /*https://octobercms.com/docs/plugin/components#dropdown-properties
		*  The method should have a name in the following format: get*Property*Options()
		*  where Property is the property name
		*/
		///$instances = CompetenceModel::where("Enabled","=","1")->get();
		$instances = CompetenceModel::where("Name","!=","")->get();//WORKS or add Enabled

        $array_dropdown = ['0'=>'- select Instance - '];//text in dropdown

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }
    
    /* // look for locked module assignments
    private function getModules($freshData)
    {
        $moduleId = null;
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
			$mod->locked=$item['locked'];
			$mod->items =$item['module_items'];// only need this obj ?
			//module_items[i].content[0].title & html_url
			//module_items[i].content[0] has points_possible & tags
			
            $simpleModules[] = $mod;
        }
        ///$this->page['rawData'] = json_encode($simpleModules);
		return $simpleModules;

    }
    */
}
