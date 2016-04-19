<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\VariableOperand;
use Delphinium\Blade\Classes\Rules\Value;

/**
 * A Multiplication Arithmetic Operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class Multiplication extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        /** @var VariableOperand $left */
        /** @var VariableOperand $right */
        list($left, $right) = $this->getOperands();

        return new Value($left->prepareValue($context)->multiply($right->prepareValue($context)));
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
