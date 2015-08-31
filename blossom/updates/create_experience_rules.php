<?php

namespace Delphinium\Blossom\Updates;

use October\Rain\Database\Updates\Migration;
use \Delphinium\Xylum\Models\ComponentTypes;
use \Delphinium\Xylum\Models\ComponentRules;
use \Delphinium\Blade\Classes\Rules\RuleBuilder;
use \Delphinium\Blade\Classes\Rules\RuleGroup;

class CreateRules extends Migration {

    public function up() {
        $rb = new RuleBuilder;

        $submisions = $rb->create('current_user_submissions', 'submission',
        $rb['submission']['score']->greaterThan($rb['score_threshold']),
        [
            $rb['(bonus)']->assign($rb['(bonus)']->add($rb['points']))
        ]);
        
        $rb['(bonus)'] = 0;
        $rb['submission']['score'] = 0;
        $rb['score_threshold'] = 0;
        $rb['points'] = 0;

        $rg = new RuleGroup('experience');
        $rg->add($submisions);
        $rg->saveRules();

//        $rule = $rb->create('allscores', 'submission', $rb->tautology(), [$rb['(total_score)']->assign($rb['(total_score)']->add($rb['score']))]);
//
//        $rb['(total_score)'] = 0;
//        $rb['score'] = 0;
//
//        $rg = new RuleGroup('experienceRules');
//        $rg->add($rule);
//        $rg->saveRules();
//
//
//        $scorebonus = $rb->create('scorebonus', 'submission', $rb['score']->greaterThan($rb['score_threshhold']), [$rb['(bonus)']->assign($rb['(bonus)']->add($rb['base_bonus']))]);
//
//        $rb['base_bonus'] = 15;
//        $rb['score_threshold'] = 90;
//        $rb['(bonus)'] = 0; // this is important to get the bonus to default to 0 on first calculation
//
//        $rg2 = new RuleGroup('bonusRules');
//        $rg2->add($scorebonus);
//        $rg2->saveRules();
        
        
        //inform Xylum of the rules this component will be using
        $cType = ComponentTypes::firstOrNew(array('type' => 'experience'));
        $cType->type = 'experience';
        $cType->save();

        $ruleId = $submisions->getId();
        $componentRule = ComponentRules::firstOrNew(array('rule_id' => $ruleId, 'component_id' => $cType->id));
        $componentRule->rule_id = $ruleId;
        $componentRule->component_id = $cType->id;
        $componentRule->save();
    }

    public function down() {
        //TODO: figure out what to do on down
    }

}
