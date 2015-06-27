<?php

namespace Delphinium\Blade\Classes\Rules;

use \Model;
use \Delphinium\Blade\Models\Action as ActionModel;

/**
 * 
 *
 * @author Daniel Clark
 */
class AssignAction implements ISavable {

    protected $variable;
    protected $value;

    public function __construct(Variable $var, Variable $value) {
        $this->variable = $var;
        $this->value = $value;
    }

    public function execute($context) {
        $context[$this->variable->getName()] = $this->value->prepareValue($context)->getValue();
    }

    public function matches(Model $model) {
        return $this->variable->getName() === $model->variable_name &&
                $this->value->matches($model->variable);
    }

    public function save(Model $parent, Model $parent_rule, $order) {
        $model = new ActionModel([
            'variable_name' => $this->variable->getName(),
            'order' => $order]);
        $model->rule()->associate($parent);
        $model->save();

        $this->value->save($model, $parent_rule, 0);
    }

}
