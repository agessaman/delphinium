<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A Contains comparison operator.
 *
 * @deprecated Please use SetContains or StringContains operators instead.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class Contains extends VariableOperator implements Proposition
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

        $left = $left->prepareValue($context);

        if (is_array($left->getValue())) {
            trigger_error('Contains operator is deprecated, please use SetContains', E_USER_DEPRECATED);

            return $left->getSet()->setContains($right->prepareValue($context));
        } else {
            trigger_error('Contains operator is deprecated, please use StringContains', E_USER_DEPRECATED);

            return $left->stringContains($right->prepareValue($context));
        }
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
