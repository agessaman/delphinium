<?php

/*
 * This file is part of the Delphinium Project
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Delphinium\Blade\Classes\Rules;

use Delphinium\Blade\Classes\Rules\RuleBuilder\Variable;
use Delphinium\Blade\Classes\Rules\Operator\LogicalAnd;
use Delphinium\Blade\Classes\Rules\Operator\LogicalOr;
use Delphinium\Blade\Classes\Rules\Operator\LogicalNot;
use Delphinium\Blade\Classes\Rules\Operator\LogicalXor;

//Original Author: Justin Hileman <justin@justinhileman.info> of the Ruler project
/**
 * RuleBuilder.
 *
 * The RuleBuilder provides an easy interface for creating rules
 * 
 * @author Daniel Clark
 */
class RuleBuilder implements \ArrayAccess
{
    private $variables          = [];
    private $operatorNamespaces = [];

    /**
     * Create a Rule with the given propositional condition.
     *
     * @param Proposition $condition Propositional condition for this Rule
     * @param callback    $action    Action (callable) to take upon successful Rule execution (default: null)
     *
     * @return Rule
     */
    public function create($name, $datatype, Proposition $condition, $actions = null)
    {
        return new Rule($name, $datatype, $condition, $actions);
    }

    /**
     * Register an operator namespace.
     *
     * Note that, depending on your filesystem, operator namespaces are most likely case sensitive.
     *
     * @throws \InvalidArgumentException
     *
     * @param string $namespace Operator namespace
     *
     * @return RuleBuilder
     */
    public function registerOperatorNamespace($namespace)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException('Namespace argument must be a string');
        }

        $this->operatorNamespaces[$namespace] = true;

        return $this;
    }

    /**
     * Create a logical AND operator proposition.
     *
     * @param Proposition $prop      Initial Proposition
     * @param Proposition $prop2,... Optional unlimited number of additional Propositions
     *
     * @return Operator\LogicalAnd
     */
    public function logicalAnd(Proposition $prop, Proposition $prop2 = null)
    {
        return new LogicalAnd(func_get_args());
    }

    /**
     * Create a logical OR operator proposition.
     *
     * @param Proposition $prop      Initial Proposition
     * @param Proposition $prop2,... Optional unlimited number of additional Propositions
     *
     * @return Operator\LogicalOr
     */
    public function logicalOr(Proposition $prop, Proposition $prop2 = null)
    {
        return new LogicalOr(func_get_args());
    }

    /**
     * Create a logical NOT operator proposition.
     *
     * @param Proposition $prop Exactly one Proposition
     *
     * @return Operator\LogicalNot
     */
    public function logicalNot(Proposition $prop)
    {
        return new LogicalNot(array($prop));
    }

    /**
     * Create a logical XOR operator proposition.
     *
     * @param Proposition $prop      Initial Proposition
     * @param Proposition $prop2,... Optional unlimited number of additional Propositions
     *
     * @return Operator\LogicalXor
     */
    public function logicalXor(Proposition $prop, Proposition $prop2 = null)
    {
        return new LogicalXor(func_get_args());
    }

    /**
     * Check whether a Variable is already set.
     *
     * @param string $name The Variable name
     *
     * @return boolean
     */
    public function offsetExists($name)
    {
        return isset($this->variables[$name]);
    }

    /**
     * Retrieve a Variable by name.
     *
     * @param string $name The Variable name
     *
     * @return Variable
     */
    public function offsetGet($name)
    {
        if (!isset($this->variables[$name])) {
            $this->variables[$name] = new Variable($this, $name);
        }

        return $this->variables[$name];
    }

    /**
     * Set the default value of a Variable.
     *
     * @param string $name  The Variable name
     * @param mixed  $value The Variable default value
     *
     * @return Variable
     */
    public function offsetSet($name, $value)
    {
        $this->offsetGet($name)->setValue($value);
    }

    /**
     * Remove a defined Variable from the RuleBuilder.
     *
     * @param string $name The Variable name
     */
    public function offsetUnset($name)
    {
        unset($this->variables[$name]);
    }

    /**
     * Find an operator in the registered operator namespaces.
     *
     * @throws \LogicException If a matching operator is not found.
     *
     * @param string $name
     *
     * @return string
     */
    public function findOperator($name)
    {
        $operator = ucfirst($name);
        foreach (array_keys($this->operatorNamespaces) as $namespace) {
            $class = $namespace . '\\' . $operator;
            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \LogicException(sprintf('Unknown operator: "%s"', $name));
    }
}
