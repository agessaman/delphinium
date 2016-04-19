<?php

namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Set;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A Set Intersection Operator
 *
 * @author Jordan Raub <jordan@raub.me>
 */
class Intersect extends VariableOperator implements VariableOperand
{
    public function prepareValue(IContext $context)
    {
        $intersect = null;
        /** @var VariableOperand $operand */
        foreach ($this->getOperands() as $operand) {
            if (!$intersect instanceof Set) {
                $intersect = $operand->prepareValue($context)->getSet();
            } else {
                $set = $operand->prepareValue($context)->getSet();
                $intersect = $intersect->intersect($set);
            }
        }

        return $intersect;
    }

    protected function getOperandCardinality()
    {
        return static::MULTIPLE;
    }
}
