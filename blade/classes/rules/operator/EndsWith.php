<?php
namespace Delphinium\Blade\Classes\Rules\Operator;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Classes\Rules\VariableOperand;

/**
 * A EndsWith comparison operator.
 *
 * @author Cornel Les <thebogu@gmail.com>
 */
class EndsWith extends VariableOperator implements Proposition
{
    /**
     * @param IContext $context IContext with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(IContext $context)
    {
        /** @var VariableOperand $left */
        /** @var VariableOperand $right */
        list($left, $right) = $this->getOperands();

        return $left->prepareValue($context)->endsWith($right->prepareValue($context));
    }

    protected function getOperandCardinality()
    {
        return static::BINARY;
    }
}
