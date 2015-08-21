<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Delphinium\Blade\Classes\Rules\Operator;
use Delphinium\Blade\Classes\Rules\Proposition;

/**
 * Description of Tautology
 *
 * @author Daniel
 */
class Tautology extends Operator implements Proposition {
    public function evaluate(\Delphinium\Blade\Classes\Rules\IContext $context) {
        return true;
    }
}
