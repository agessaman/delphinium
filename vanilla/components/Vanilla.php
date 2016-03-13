<?php namespace Delphinium\Vanilla\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Vanilla\Models\Vanilla as VanillaModel;

class Vanilla extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'vanilla Component',
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
            else create dynamicInstance, save new instance, show it
            
            Dont forget have the component set up from Here:
            https://github.com/ProjectDelphinium/delphinium/wiki/3.-Setting-up-a-Project-Delphinium-Dev-environment-on-localhost
            */
            if (!isset($_SESSION)) { session_start(); }

            $courseID = $_SESSION['courseID'];
            // if instance has been set
            if( $this->property('instance') )
            {
                //instance set in CMS getInstanceOptions()
                $config = VanillaModel::find($this->property('instance'));
                $config->course_id = $_SESSION['courseID'];//$course->id;
                $config->save();//update original record now in case it did not have course

            } else {
                // if copy has a name 
                $copyLength = strlen($this->property('copy_id'));
                if($copyLength > 0 )
                {
                    // find all matching course 
                    $instances = VanillaModel::where('course_id','=', $courseID)->get();
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
                    $config = new VanillaModel;// db record
                    $config->name = 'dynamic_';//+ total records count?
                    // add your fields
                    //$config->size = 'Medium';
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
            
            // include any css or javascript here
            $this->addCss("/plugins/delphinium/vanilla/assets/css/bootstrap.min.css");
            $this->addJs("/plugins/delphinium/vanilla/assets/javascript/jquery.min.js");
            $this->addJs("/plugins/delphinium/vanilla/assets/javascript/bootstrap.min.js");
            
            // include the backend form with instructions here
            if(stristr($roleStr, 'Instructor'))
			{
				//https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
				// Build a back-end form with the context of 'frontend'
				$formController = new \Delphinium\Vanilla\Controllers\Vanilla();
				$formController->create('frontend');
				
				// Append the formController to the page
				$this->page['form'] = $formController;
                //this is the primary key of the record you want to update
                $this->page['recordId'] = $config->id;
                
                // Instructions page
                $instructions = $formController->makePartial('instructions');
                $this->page['instructions'] = $instructions;
                
                //other code specific to instructor view goes here
            }
            
            if(stristr($roleStr, 'Learner'))
			{
            //code specific to the student view goes here
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
        * Fills the Configuration [dropdown] in CMS 
		*/
		$instances = VanillaModel::all();
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
        $data = post('Vanilla');//('Competencies');
        $did = intval($data['id']);
        $config = VanillaModel::find($did);
        $config->name = $data['name'];
        //echo json_encode($config);//($data);//
        
		// add your fields to update
        //$config->Size = $data['Size'];

		$config->course_id = $data['course_id'];//hidden
        $config->copy_id = $data['copy_id'];//hidden
		$config->save();// update original record 
		return json_encode($config);// back to instructor
        
    }
    /* End of class */
}