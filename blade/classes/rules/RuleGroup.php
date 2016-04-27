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

use Delphinium\Blade\Models\RuleGroup as RuleGroupModel;
use Delphinium\Blade\Classes\Rules\Context;
/**
 *
 * @author Daniel Clark
 */
class RuleGroup implements IPersistent, IRuleGroup {
    private $name;
    private $rules = [];
    private $dbid;
    private $model;
    
    public function __construct($name) {
        $this->name = $name;
        $this->getRules();
    }
    
    public function getId() {
        return $this->dbid;
    }
    
    public function add(Rule $rule) {
        if (!$this->contains($rule)) {
            $this->rules[] = $rule;
        }
    }
    
    public function contains(Rule $rule) {
        foreach($this->rules as $r) {
            if ($r->getId() == $rule->getId() &&
                    $r->getName() == $rule->getName()) {
                return true;
            } 
        }
        
        return false;
    }
    
    public function getRules() {
        if (!$this->exists()) return [];
        $this->rules = [];
        $model = $this->model;
        
        foreach ($model->rules as $r) {
            $this->rules[] = $r->toExecutable();
        }
        
        return $this->rules;
    }

    public function delete() {
        //remember to unset dbid and model
        $this->model->delete();
        $this->dbid = null;
        $this->model = null;
    }

    public function exists() {
        if (isset($dbid)) return true;
        $model = RuleGroupModel::where(['name' => $this->name])->first();
        
        if(isset($model)) {
            $this->dbid = $model->id;
            $this->model = $model;
            return true;
        }
        return false;
    }

    public function findOrCreate() {
        if(!$this->exists()) {
            $this->save();
        }
    }
    
    private function save() {
        $model = new RuleGroupModel(['name' => $this->name]);
        $model->save();
        $this->dbid = $model->id;
        $this->model = $model;
        $this->saveRules();
    }
    
    public function saveRules() {
        $this->findOrCreate();
        foreach($this->rules as $rule) {
            $rule->findOrCreate();
        }
        
        $ids = array_map(function ($r) {return $r->getId();}, $this->rules);
        
        $this->model->rules()->sync($ids);
    }
}
