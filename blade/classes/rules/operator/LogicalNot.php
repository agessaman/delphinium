<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;

/**
 * A logical NOT operator.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class LogicalNot extends LogicalOperator
{
    /**
     * @param IContext $context IContext with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(IContext $context)
    {
        /** @var Proposition $operand */
        list($operand) = $this->getOperands();

        return !$operand->evaluate($context);
    }

    protected function getOperandCardinality()
    {
        return static::UNARY;
    }
}
