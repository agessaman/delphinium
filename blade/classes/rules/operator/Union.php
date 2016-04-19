<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Set;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A Set Union Operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class Union extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        $union = new Set(array());
        /** @var VariableOperand $operand */
        foreach ($this->getOperands() as $operand) {
            $set = $operand->prepareValue($context)->getSet();
            $union = $union->union($set);
        }

        return $union;
    }

    protected function getOperandCardinality()
    {
        return static::MULTIPLE;
    }
}
