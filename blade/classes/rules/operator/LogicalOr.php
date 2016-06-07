<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;

/**
 * A logical OR operator.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class LogicalOr extends LogicalOperator
{
    /**
     * @param IContext $context IContext with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(IContext $context)
    {
        /** @var Proposition $operand */
        foreach ($this->getOperands() as $operand) {
            if ($operand->evaluate($context) === true) {
                return true;
            }
        }

        return false;
    }

    protected function getOperandCardinality()
    {
        return static::MULTIPLE;
    }
}
