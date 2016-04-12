<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\EasterEggs as EasterEggsModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;

class EasterEggs extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'EasterEggs',
            'description' => 'Find the easter eggs!'
        ];
    }

    public function defineProperties()
    {
        return [
            'instance'   => [
                'title'             => 'EasterEggs Configuration',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
            ]
        ];
    }

    public function onRun()
    {
        try
        {
            
            if (!isset($_SESSION)) { session_start(); }

            $courseID = $_SESSION['courseID'];

            //use the instance set in CMS dropdown
            $config = EasterEggsModel::find($this->property('instance'));
            $config->save();//update original record now in case it did not have course

           
            // use the record in the component and frontend form 
            $this->page['config'] = json_encode($config);
            
            /** get roles, a comma delimited string
             * check if Student
             * if not then set to Instructor. disregard any other roles?
             * role is used to determine functions and display options
             */
            $roleStr = $_SESSION['roles'];
            
            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
            $this->page['role'] = $roleStr;// only one or the other

            $path = \Config::get("app.url");
            $this->page['path'] = $path;

            $menu = $config->menu;
            $this->page['menu'] = $menu;

            $exComp = new ExperienceComponent();
            $points = $exComp->getUserPoints();
            $this->page['current_grade'] = $points;
            
            // include your css note: bootstrap.min.css is part of minimal layout
            $this->addCss("/plugins/delphinium/blossom/assets/css/eastereggs.css");
            // javascript had to be added to default.htm to work correctly
            //$this->addJs("/plugins/delphinium/EasterEggs/assets/javascript/jquery.min.js");
            
            // include the backend form with instructions for instructor.htm
            if(stristr($roleStr, 'Instructor'))
            {
                //https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
                // Build a back-end form with the context of 'frontend'
                $formController = new \Delphinium\Blossom\Controllers\EasterEggs();
                $formController->create('frontend');
                
                //this is the primary key of the record you want to update
                $this->page['recordId'] = $config->id;
                // Append the formController to the page
                $this->page['form'] = $formController;
                
                // Append Instructions page
                $instructions = $formController->makePartial('instructions');
                $this->page['instructions'] = $instructions;
                
                //code specific to instructor.htm goes here
            }
            
            if(stristr($roleStr, 'Learner'))
            {
                //code specific to the student.htm goes here
            }
            // code used by both goes here
            
        // Error handling requires nonlti.htm
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
        * Fill the Competencies Configuration [dropdown] for CMS
        */
        $instances = EasterEggsModel::all();//where("Name","!=","")->get();
        $array_dropdown = ['0'=>'- select Instance - '];//id, text in dropdown

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->name;
        }
        return $array_dropdown;
    }

    
    public function onUpdate()
    {
        $data = post('EasterEggs');//component name
        $did = intval($data['id']);// convert string to integer
        $config = EasterEggsModel::find($did);// retrieve existing record
        $config->name = $data['name'];// change to new data
        //echo json_encode($config);//($data);// testing
        
        // add your fields to update
        $config->custom = $data['custom'];
        $config->save();// update original record 
        return json_encode($config);// back to instructor view
    }
}