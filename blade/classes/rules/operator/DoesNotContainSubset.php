<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A DoesNotContainSubset comparison operator.
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class DoesNotContainSubset extends VariableOperator implements Proposition
{
    /**
     * @param IContext $context IContext with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(IContext $context)
    {
        /** @var VariableOperand $left */
        /** @var VariableOperand $right */
        list($left, $right) = $this->getOperands();

        return $left->prepareValue($context)->getSet()
            ->containsSubset($right->prepareValue($context)->getSet()) === false;
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
