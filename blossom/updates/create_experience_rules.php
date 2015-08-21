<?php

namespace Delphinium\Blossom\Updates;

use October\Rain\Database\Updates\Migration;
use \Delphinium\Xylum\Models\ComponentTypes;
use \Delphinium\Xylum\Models\ComponentRules;
use \Delphinium\Blade\Classes\Rules\RuleBuilder;
use \Delphinium\Blade\Classes\Rules\RuleGroup;

class CreateRules extends Migration {

    public function up() {
//        $rb = new RuleBuilder;
//
//        $rb->create('allscores', 'submission', $rb->tautology(), [$rb['(total_score)']->assign($rb['(total_score)']->add($rb['score']))]);

//        $rb['(total_score)'] = 0;
//        $rb['score'] = 0;
        
        
        
//        $rg2 = new RuleGroup('bonusRules');
//        $rg2->add($rule1);
//        $rg2->saveRules();
//        
//        //inform Xylum of the rules this component will be using
//        $cType = ComponentTypes::firstOrNew(array('type' => 'bonus'));
//        $cType->type = 'experience';
//        $cType->save();
//        
//        $ruleId = $rule1->getId();
//        $componentRule = ComponentRules::firstOrNew(array('rule_id' => $ruleId, 'component_id' => $cType->id));
//        $componentRule->rule_id = $ruleId;
//        $componentRule->component_id = $cType->id;
//        $componentRule->save();
    }

    public function down() {
        //TODO: figure out what to do on down
    }

}
