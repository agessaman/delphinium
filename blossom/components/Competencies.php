<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\RequestObjects\AssignmentsRequest;
use Delphinium\Roots\Enums\CommonEnums\ActionType;
use Delphinium\Roots\RequestObjects\ModulesRequest;

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
            'Competencies' => [
                'title'        => 'Number of Competencies',
                'description'  => 'Enter number of Competencies',
                'type'         => 'string',
                'default'      => '3',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The number of Competencies is required and should be integer.'
            ],
            
            
            'Animate' => [
                'title'        => 'Animate',
                'type'         => 'dropdown',
                'default'      => 'true',
                'options'      => ['true'=>'True', 'false'=>'False']
            ],

            'Size' => [
                'title'        => 'Size',
                'type'         => 'dropdown',
                'default'      => 'Medium',
                'options'      => ['Small'=>'Small', 'Medium'=>'Medium', 'Large'=>'Large']
            ]
            
        ];
    }

    public function onRender()
    {
        $this->page['competencies'] = $this->property('Competencies');
        $this->page['competenciesAnimate'] = $this->property('Animate');
        $this->page['competenciesSize'] = $this->property('Size');
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/competencies.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");

        $this->roots = new Roots();
        $req = new AssignmentsRequest(ActionType::GET);
        
        $res = $this->roots->assignments($req);
        
        
        
        $assignments = array();
        foreach ($res as $assignment) {
            $assignment_array = array('assignment_id' => $assignment["assignment_id"], 
                                      'quiz_id' => $assignment["quiz_id"],
                                      'tags' => "");
            array_push($assignments, $assignment_array);
        }


        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = true;
        $includeContentItems = true;
        $module = null;
        $moduleItem = null;
        $freshData = true;

         $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
                $includeContentDetails, $module, $moduleItem , $freshData) ;
        
        $res = $this->roots->modules($req);
        
        $tags = array();
        foreach ($res as $module) {
            foreach ($module->relations as $items) {
                foreach ($items as $item) {
                    foreach ($item->relations as $contents) {
                        foreach ($contents as $content) {
                            $tag_array = array('content_id' => $content->attributes["content_id"], 
                                                'tags' => $content->attributes["tags"],);
                            array_push($tags, $tag_array);  
                        }
                    }
                }
            }
        }


        foreach ($assignments as $i => $assignment) {
            foreach ($tags as $tag) {
                if($assignment["quiz_id"]==$tag["content_id"]){
                    $assignment["tags"] = $tag["tags"];
                    $assignments[$i]= $assignment;
                }
            }
        }


         

    }
}