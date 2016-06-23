<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Value;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A Ceil Math Operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class Ceil extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        /** @var VariableOperand $operand */
        list($operand) = $this->getOperands();

        return new Value($operand->prepareValue($context)->ceil());
    }

    protected function getOperandCardinality()
    {
        return static::UNARY;
    }
}
