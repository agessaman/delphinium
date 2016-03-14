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
                'title'             => 'Eggs Configuration',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
            ]
        ];
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
            $array_dropdown[$instance->id] = $instance->Name;
        }
        return $array_dropdown;
    }

    public function onSave()
    {
        $config = EasterEggsModel::find($this->property('instance'));
        $data = post('EasterEggs');
        
        $config->Name = $data['Name'];
        $config->course_id = $data['course_id'];
        $config->copy_id = $data['copy_id'];
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
        $config->Name = 'New Instance';//+ total records count?
        $config->course_id = $_SESSION['courseID'];// or null
        $config->copy_id = 1;
        $config->save();// create new record 
    }

}