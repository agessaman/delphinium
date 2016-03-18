<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\EasterEggs as EasterEggsModel;

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

    public function onRender()
    {
        //$roots = new Roots();
        //$course = $roots->getCourse();
        //$this->page['course'] = json_encode($course);
        //$course->id or $_SESSION['courseID']
        
        $this->page['crsid'] = $_SESSION['courseID'];// test
        
        /*
        When a component wakes up in a course, it needs to know what course it is assigned to
        and which copy it is so it can configure itself properly.
        
        if courseID is available, get records matching course ID
            could be multiple
        
        Using this information, it can select the proper instance to load with the appropriate configuration data.
        
        */
        
        //instance set in CMS getInstanceOptions()
        $config = EastereggsModel::find($this->property('instance'));
        //Name is just for instances drop down. Use in component display?
        
        // copy_id is part of $config
        //add $course->id to $config for form field
        $config->course_id = $_SESSION['courseID'];//$course->id;
        $this->page['config'] = json_encode($config);
        //$config->save();// update original record now ???
        
        // comma delimited string ?
        if (!isset($_SESSION)) { session_start(); }
        $roleStr = $_SESSION['roles'];
        $this->page['role'] = $roleStr;


        $path = \Config::get("app.url");
        $this->page['path'] = $path;

        $menu = $config->menu;
        $this->page['menu'] = $menu;
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/eastereggs.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/eastereggs.css");
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

    public function onSave()
    {
        $config = EasterEggsModel::find($this->property('instance'));
        $data = post('EasterEggs');
        
        $config->name = $data['name'];
        $config->course_id = $data['course_id'];
        $config->copy_id = $data['copy_id'];
        $config->menu = $data['menu'];
        $config->save();// update original record 

        return json_encode($config);
    }
    
    // test: for controller.formExtendFields
    public function getConfig()
    {
        $config = EasterEggsModel::find($this->property('instance'));
        return $config;
    }
    
    public function dynamicInstance()
    {
        $config = new EasterEggsModel;// db record
        $config->name = 'New Instance';//+ total records count?
        $config->course_id = $_SESSION['courseID'];// or null
        $config->copy_id = 1;
        $config->menu = false;
        $config->save();// create new record 
    }

}