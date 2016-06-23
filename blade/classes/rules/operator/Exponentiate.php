<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Value;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * An Exponentiate Math Operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class Exponentiate extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        /** @var VariableOperand $left */
        /** @var VariableOperand $right */
        list($left, $right) = $this->getOperands();

        return new Value($left->prepareValue($context)->exponentiate($right->prepareValue($context)));
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
