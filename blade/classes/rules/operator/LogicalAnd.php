<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Models\Operator;

/**
 * A logical AND operator.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class LogicalAnd extends LogicalOperator
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
            if ($operand->evaluate($context) === false) {
                return false;
            }
        }

        return true;
    }

    protected function getOperandCardinality()
    {
        return static::MULTIPLE;
    }
}
