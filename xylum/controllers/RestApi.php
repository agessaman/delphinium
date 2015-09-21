<?php

namespace Delphinium\Xylum\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Blade\Classes\Rules\RuleBuilder;
use Delphinium\Blade\Classes\Rules\RuleGroup;

class RestApi extends Controller {

    public function addComponentsToCourse() {
        $instances = \Input::get('instances');
        
        foreach($instances as $item)
        {
            echo json_encode($item);
            $ruleGroup = new RuleGroup('bonus-' . $item['course_id']);
            if(!$ruleGroup->exists())
            {
                $ruleGroup->saveRules();
            }
            
            //TODO
            //Loop through each item and add the rules to the rule group. 
            //How will we know the rules each component has?
        }
    }

}
