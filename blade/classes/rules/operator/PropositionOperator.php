<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Classes\Rules\Operator as BaseOperator;

/**
 * @author Jordan Raub <jordan@raub.me>
 */
abstract class PropositionOperator extends BaseOperator
{
    public function addOperand($operand)
    {
        $this->addProposition($operand);
    }

    public function addProposition(Proposition $operand)
    {
        if (static::UNARY == $this->getOperandCardinality()
            && 0 < count($this->operands)
        ) {
            throw new \LogicException(get_class($this) . " can only have 1 operand");
        }
        $this->operands[] = $operand;
    }
}
