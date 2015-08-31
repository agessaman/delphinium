<?php

namespace Delphinium\Blade\Classes\Rules\Operator;
use Delphinium\Blade\Classes\Rules\Proposition;
use Delphinium\Blade\Classes\Rules\Operator;
/**
 * @author Daniel Clark
 */
class Contradiction extends Operator implements Proposition {
    public function evaluate(\Delphinium\Blade\Classes\Rules\IContext $context) {
        return false;
    }
    
    protected function getOperandCardinality()
    {
        
    }
    
    public function addOperand($operand)
    {
        
    }
}
