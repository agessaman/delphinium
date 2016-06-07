<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A Set Contains comparison operator.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class SetDoesNotContain extends VariableOperator implements Proposition
{
    /**
     * @param Context $context Context with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(IContext $context)
    {
        /** @var VariableOperand $left */
        /** @var VariableOperand $right */
        list($left, $right) = $this->getOperands();

        return $left->prepareValue($context)->getSet()->setContains($right->prepareValue($context)) === false;
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
