<?php

namespace Delphinium\Blade\Classes\Data;

use Delphinium\Blade\Classes\Rules\Rule;
use Delphinium\Blade\Classes\Rules\IContext;

/**
 * A Decorator of the Ruler Context object that seperates out
 * data that should not go into the main Context.
 * (Default variable values and custom variable values).
 *
 * @author Daniel Clark
 */
class RuleContext implements IContext {

    private $context;
    private $rule;
    private $keys = [];
    private $values = [];
    private $unset = [];

    public function __construct(Rule $rule, IContext $context) {
        $this->rule = $rule;
        $this->context = $context;
    }

    public function offsetGet($name) {
        if (isset($this->unset[$name])) {
            return null;
        }

        $value = $this->context->offsetGet($name);
        return (isset($value)) ? $value :
                $value = $this->rule->getVariableDefaultValue($name);
    }

    public function offsetExists($offset) {
        $value = $this->offsetGet($offset);
        return isset($value);
    }

    public function offsetSet($offset, $value) {
        $this->context->offsetSet($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->unset[$offset] = true;
        $this->context->offsetUnset($offset);
    }

    public function share($callable) {
        return $this->context->share($callable);
    }

    public function protect($callable) {
        return $this->context->protect($callable);
    }

    public function raw($name) {
        return $this->context->raw($name);
    }

    public function keys() {
        return array_merge($this->rule->getKeys(), $this->context->keys());
    }

    public function getData() {
        return $this->context->getData();
    }

    public function isExcluded() {
        return $this->context->isExcluded();
    }

    public function setExcluded($excluded) {
        $this->context->setExcluded($excluded);
    }

}
