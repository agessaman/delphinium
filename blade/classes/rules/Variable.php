<?php

namespace Delphinium\Blade\Classes\Rules;

use \Delphinium\Blade\Models\Variable as VariableModel;
use \Model;

/**
 * A propositional Variable.
 *
 * Variables are placeholders in Propositions and Comparison Operators. During
 * evaluation, they are replaced with terminal Values, either from the Variable
 * default or from the current IContext.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class Variable implements VariableOperand, ISavable {

    private $name;
    private $value;
    private $custom = false;
    // variables for array access functionality
    protected $parent;
    protected $children = [];

    /**
     * Variable class constructor.
     *
     * @param string $name  Variable name (default: null)
     * @param mixed  $value Default Variable value (default: null)
     */
    public function __construct($name = null, $value = null) {
        if (gettype($value) == 'array') {
            throw new \LogicException('You may not default a variable to an array');
        }

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Return the Variable name.
     *
     * @return string Variable name
     */
    public function getName() {
        return $this->name;
    }
    
    public function getParent() {
        return $this->parent;
    }

    /**
     * Set the default Variable value.
     *
     * @param mixed $value The default Variable value
     */
    public function setValue($value) {
        if (gettype($value) == 'array') {
            throw new \LogicException('You may not default a variable to an array');
        }

        $this->value = $value;
    }

    // Aref is short for array reference
    // aref chains are built using the rule builder
    // "$rb['submission']['score']" is an example
    // the 'submission' part is the parent, 'score' is the child
    public function addArefChild(Variable $child) {
        if (isset($value)) {
            throw new LogicException('Cannot add array-ref child to variable with a set value.');
        }

        if (isset($child->name)) {
            $this->children[$child->name] = $child;
            $child->parent = $this;
        } else {
            throw new LogicException('Cannot add an unnamed array-ref child');
        }
    }

    /**
     * Get the default Variable value.
     *
     * @return mixed Variable value
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Prepare a Value for this Variable given the current IContext.
     *
     * @param IContext $context The current IContext
     *
     * @return Value
     */
    public function prepareValue(IContext $context) {
        if (isset($this->parent)) {
            $arr = $this->parent->prepareValue($context)->getValue();
            if (isset($arr)) {
                $value = $arr[$this->name];
            } else {
                $value = null;
            }
        } elseif (isset($this->name) && isset($context[$this->name])) {
            $value = $context[$this->name];
        } elseif ($this->value instanceof VariableOperand) {
            $value = $this->value->prepareValue($context);
        } else {
            $value = $this->value;
        }

        return ($value instanceof Value) ? $value : new Value($value);
    }

    //author Daniel Clark
    public function save(Model $parent, Model $parent_rule, $order) {
        if (!isset($this->value)) {
            // variable exists just to pull its value from the context
            $var = new VariableModel([
                'name' => $this->name,
                'order' => $order,
                'custom' => false
            ]);

            $var->save();
            $this->saveParent($parent_rule, $var);
        } else if (gettype($this->value) != 'object') {
            // there is a default value assigned for this variable
            $var = new VariableModel([
                'name' => $this->name,
                'order' => $order,
                'custom' => true,
                'default_value' => (string) $this->value,
                'datatype' => gettype($this->value)]);

            $var->save();
            $this->saveParent($parent_rule, $var);
        } else {
            // value is a wrapped Operator
            $var = new VariableModel([
                'name' => $this->name,
                'order' => $order,
                'custom' => false
            ]);

            $var->save();

            $this->value->save($var, $parent_rule, 0);
        }

        $parent->variable()->save($var);
        $parent_rule->variables()->save($var);
    }

    private function saveParent(Model $parent_rule, Model $var_model) {
        $p = $this->parent;
        if (!isset($p)) {
            return;
        }
        
        $model = $parent_rule->variables()->where('name', '=', $p->name)->first();
        if (!isset($model)) {
            $model = new VariableModel([
                'name' => $p->name,
                'order' => 0,
                'custom' => false,
            ]);
            $model->save();
            $parent_rule->variables()->save($model);
        }

        $model->aref_children()->save($var_model);
    }

    public function matches(Model $model) {
        if (!($model instanceof VariableModel))
            return false;
        $exists = $model->name == $this->name;
        $child = $model->operator;
        if (isset($this->value) && gettype($this->value) == 'object') {
            $exists = $exists && $this->value->matches($child);
        }
        
        $parent = $model->aref_parent;
        if (isset($this->parent)) {
            $exists = $exists && $this->parent->matches($parent);
        }
        
        return $exists;
    }

}
