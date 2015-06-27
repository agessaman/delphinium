<?php

/*
 * This file is part of the Ruler package, an OpenSky project.
 *
 * (c) 2011 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * Variable class constructor.
     *
     * @param string $name  Variable name (default: null)
     * @param mixed  $value Default Variable value (default: null)
     */
    public function __construct($name = null, $value = null) {
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

    /**
     * Set the default Variable value.
     *
     * @param mixed $value The default Variable value
     */
    public function setValue($value) {
        $this->value = $value;
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
        if (isset($this->name) && isset($context[$this->name])) {
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
        $var = null;
        if (!isset($this->value)) {
            $var = new VariableModel([
                'name' => $this->name,
                'order' => $order,
                'custom' => false
            ]);

            $var->save();
        } else if (gettype($this->value) != 'object') {
            $var = new VariableModel([
                'name' => $this->name,
                'order' => $order,
                'custom' => true,
                'default_value' => (string) $this->value,
                'datatype' => gettype($this->value)]);

            $var->save();
        } else {
             // value is a Variable or Operator
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

    public function matches(VariableModel $model) {
        $exists = $model->name == $this->name;
        $child = $model->getChild();
        if (isset($child)) {
            $exists = $exists && $this->value->matches($child);
        }
        return $exists;
    }

}
