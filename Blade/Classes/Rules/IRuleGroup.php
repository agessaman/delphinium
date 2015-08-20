<?php

namespace Delphinium\Blade\Classes\Rules;

/**
 * @author Daniel Clark
 */
interface IRuleGroup {

    public function add(Rule $rule);

    public function contains(Rule $rule);

    public function getRules();
}
