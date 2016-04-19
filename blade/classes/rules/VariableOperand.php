<?php

namespace Delphinium\Blade\Classes\Rules;

/**
 * @author Jordan Raub <jordan@raub.me>
 */
interface VariableOperand
{
    /**
     * @param IContext $context
     *
     * @return Value
     */
    public function prepareValue(IContext $context);
}
