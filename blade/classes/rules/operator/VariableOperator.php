<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\VariableOperand;
use Delphinium\Blade\Classes\Rules\Operator as BaseOperator;

/**
 * @author Jordan Raub <jordan@raub.me>
 */
abstract class VariableOperator extends BaseOperator
{
    public function addOperand($operand)
    {
        $this->addVariable($operand);
    }

    public function addVariable(VariableOperand $operand)
    {
        if (static::UNARY == $this->getOperandCardinality()
            && 0 < count($this->operands)
        ) {
            throw new \LogicException(get_class($this) . " can only have 1 operand");
        }
        $this->operands[] = $operand;
    }
}
