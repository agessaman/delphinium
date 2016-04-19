<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

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
