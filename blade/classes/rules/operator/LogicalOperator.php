<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\Proposition;

/**
 * Logical operator base class
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
abstract class LogicalOperator extends PropositionOperator implements Proposition
{
    /**
     * array of propositions
     *
     * @param array $props
     */
    public function __construct(array $props = array())
    {
        foreach ($props as $operand) {
            $this->addOperand($operand);
        }
    }
}
