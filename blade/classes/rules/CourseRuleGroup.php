<?php

namespace Delphinium\Blade\Classes\Rules;

/**
 * A special rule group that holds rules that replace rules in other groups
 * wraps existing rule groups so that it can trasparently
 * get replacement rules instead of base rules.
 *
 * @author Daniel Clark
 */
class CourseRuleGroup implements IRuleGroup, IPersistent {
    private $dbid;
    private $model;
    private $rules = [];
    private $groups;
    
    public function __construct($courseId) {
        echo var_dump(func_get_args()); 
    }

    public function add(Rule $rule) {
        $rules[$rule->getName()] = $rule;
    }

    public function contains(Rule $rule) {
        
    }

    public function getRules() {
        
    }

    public function delete() {
        
    }

    public function exists() {
        
    }

    public function findOrCreate() {
        
    }

}
