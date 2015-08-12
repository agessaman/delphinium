<?php

namespace Delphinium\Blade\Classes\Rules\Action;

use \Model;
use \Delphinium\Blade\Models\AssignAction as ActionModel;
use Delphinium\Blade\Classes\Rules\Variable;
use Delphinium\Blade\Classes\Rules\Action;
use Delphinium\Blade\Classes\Rules\IContext;

/**
 * 
 *
 * @author Daniel Clark
 */
class AssignAction implements Action {

    protected $lvariable;
    protected $value;

    public function __construct(Variable $var, Variable $value) {
        $this->lvariable = $var;
        $this->value = $value;
    }

    public function execute(IContext $context) {
        $context[$this->lvariable->getName()] = $this->value->prepareValue($context)->getValue();
    }

    public function matches(Model $model) {
        return $this->lvariable->getName() === $model->variable_name &&
                $this->value->matches($model->variable);
    }

    public function save(Model $parent, Model $parent_rule, $order) {
        $model = new ActionModel([
            'variable_name' => $this->lvariable->getName(),
            'order' => $order]);
        $model->rule()->associate($parent);
        $model->save();

        $this->value->save($model, $parent_rule, 0);
    }
    
    public function isWhitelistAction() {
        return false;
    }
    
    public function isBlacklistAction() {
        return false;
    }

}
