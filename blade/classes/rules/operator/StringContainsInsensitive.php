<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A String Contains case insensitive comparison operator.
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class StringContainsInsensitive extends VariableOperator implements Proposition
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

        return $left->prepareValue($context)->stringContainsInsensitive($right->prepareValue($context));
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
