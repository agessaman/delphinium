<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Set;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A Symmetric Difference Set Operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class SymmetricDifference extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        /** @var VariableOperand $left */
        /** @var VariableOperand $right */
        list($left, $right) = $this->getOperands();

        return $left->prepareValue($context)->getSet()
            ->symmetricDifference($right->prepareValue($context)->getSet());
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
