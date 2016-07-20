<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Value;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A Negation Math Operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class Negation extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        /** @var VariableOperand $operand */
        list($operand) = $this->getOperands();

        return new Value($operand->prepareValue($context)->negate());
    }

    protected function getOperandCardinality()
    {
        return static::UNARY;
    }
}
