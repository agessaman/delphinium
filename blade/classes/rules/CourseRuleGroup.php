<?php

namespace Delphinium\Blade\Classes\Rules;

use Delphinium\Blade\Models\CourseRule;
use Delphinium\Blade\Models\Rule as RuleModel;

/**
 * A special rule group that holds rules that replace rules in other groups
 * wraps existing rule groups so that it can trasparently
 * get replacement rules instead of base rules.
 *
 * @author Daniel Clark
 */
class CourseRuleGroup implements IRuleGroup, IPersistent {

    private $courseid;
    private $rules = [];
    private $groups = [];
    private $dirty; // updated since last db fetch

    public function __construct($courseId, $rulegroups = []) {
        $this->courseid = $courseId;
        foreach ($rulegroups as $name) {
            $this->groups[] = new RuleGroup($name);
        }
        $this->fetchRulesFromDB();
    }

    public function add(Rule $rule) {
        $rules[$rule->getName()] = $rule;
        $dirty = true;
    }

    public function contains(Rule $rule) {
        foreach ($this->groups as $g) {
            if ($g->contains($rule)) {
                return true;
            }
        }

        $name = $rule->getName();
        return isset($this->rules[$name]) && $this->rules[$name]->getId() == $rule->getId();
    }

    private function fetchRulesFromDB() {
        $this->rules = [];

        $dbrules = RuleModel::where('course_id', '=', $this->courseid)->get();
        var_dump($dbrules);

        $this->dirty = false;
    }

    public function getRules() {
        if ($dirty) {
            $this->fetchRulesFromDB();
        }
        
        $rules = [];

        foreach ($this->groups as $g) {
            foreach ($g->getRules() as $rule) {
                $rules[$rule->getName()] = $rule;
            }
        }

        foreach (array_values($rules) as $rule) {
            $rules[$rule->getName()] = $rule;
        }

        return array_values($rules);
    }

    public function delete() {
        return; // these don't actually exist in the database, do nothing
    }

    public function exists() {
        return true;
    }

    public function findOrCreate() {
        foreach (array_values($this->rules) as $rule) {
            $rule->setCourseId($this->courseid);
            $rule->findOrCreate();
        }
    }
}
