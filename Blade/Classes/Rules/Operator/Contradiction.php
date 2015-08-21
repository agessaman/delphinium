<?php

namespace Delphinium\Blade\Classes\Rules\Operator;
use Delphinium\Blade\Classes\Rules\Proposition;

/**
 * @author Daniel Clark
 */
class Contradiction extends Operator implements Proposition {
    public function evaluate(\Delphinium\Blade\Classes\Rules\IContext $context) {
        return false;
    }
}
