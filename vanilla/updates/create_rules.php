<?php

namespace Delphinium\Vanilla\Updates;

use October\Rain\Database\Updates\Migration;
use \Delphinium\Xylum\Models\ComponentTypes;
use \Delphinium\Xylum\Models\ComponentRules;
use \Delphinium\Blade\Classes\Rules\RuleBuilder;
use \Delphinium\Blade\Classes\Rules\RuleGroup;

class CreateRules extends Migration {

    public function up() {
        $rb = new RuleBuilder;

        $rule1 = $rb->create('hasQuiz', 'assignment', $rb['quiz_id']->notEqualTo($rb['no_quiz']), [$rb['has_quiz']->assign($rb['true'])]
        );
        $rg2 = new RuleGroup('bonusRules');
        $rg2->add($rule1);
        $rg2->saveRules();
        
        //inform Xylum of the rules this component will be using
        $cType = ComponentTypes::firstOrNew(array('type' => 'bonus'));
        $cType->type = 'bonus';
        $cType->save();
        
        $ruleId = $rule1->getId();
        $componentRule = ComponentRules::firstOrNew(array('rule_id' => $ruleId, 'component_id' => $cType->id));
        $componentRule->rule_id = $ruleId;
        $componentRule->component_id = $cType->id;
        $componentRule->save();
    }

    public function down() {
        //TODO: figure out what to do on down
    }

}
