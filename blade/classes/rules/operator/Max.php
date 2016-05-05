<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Value;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A set max operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class Max extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        /** @var VariableOperand $operand */
        list($operand) = $this->getOperands();

        return new Value($operand->prepareValue($context)->getSet()->max());
    }

    protected function getOperandCardinality()
    {
        return static::UNARY;
    }
}
