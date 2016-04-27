<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;

/**
 * A logical XOR operator.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class LogicalXor extends LogicalOperator
{
    /**
     * @param IContext $context IContext with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(IContext $context)
    {
        $true = 0;
        /** @var Proposition $operand */
        foreach ($this->getOperands() as $operand) {
            if (true === $operand->evaluate($context)) {
                if (++$true > 1) {
                    return false;
                }
            }
        }

        return $true === 1;
    }

    protected function getOperandCardinality()
    {
        return static::MULTIPLE;
    }
}
