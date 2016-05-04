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

namespace Delphinium\Blade\Models;

use Model;
use \ReflectionClass;

/**
 * Operator Model
 */
class Operator extends Model implements IRuleComponent
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_operators';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['type', 'order'];

    /**
     * @var array Relations
     */
    public $morphTo = [
        'parent_model' => ['delete' => true]
    ];

    public $morphMany = [
        //'operators' => ['\Delphinium\Blade\Models\Operator', 'name' => 'parent_model'],
        'variables' => ['\Delphinium\Blade\Models\Variable', 'name' => 'parent_model']
    ];
    
    function variable() {
        return $this->morphMany('\Delphinium\Blade\Models\Variable', 'parent_model');
    }
    
    //function operator() {
    //    return $this->morphMany('\Delphinium\Blade\Models\Operator', 'parent_model');
    //}

//    public function getTree() {
//        $result = (string)$this . "\n";
//        $children = $this->getChildren();
//
//        foreach ($children as $c) {
//            $result .= $c->getTree();
//        }
//        
//        return $result;
//    }
    
    // returns a merged array of Operators and Variables in order given by $order field
    public function getChildren() {
        return $this->variables;
//        $children = $this->merge($this->operators, $this->variables);
//        usort($children, Rule::getOrderCmp());
//        return $children;
    }
    
    public function toExecutable() {
        $op = new $this->type;
        
        foreach ($this->variables as $v) {
            $op->addOperand($v->toExecutable());
        }
        return $op;
    }
    
    public function delete() {
        foreach($this->getChildren() as $c) {
            $c->delete();
        }
        
        parent::delete();
    }
    
//    private function merge($a, $b) {
//        $arr = [];
//        foreach ($a as $x) {
//            array_push($arr, $x);
//        }
//
//        foreach ($b as $y) {
//            array_push($arr, $y);
//        }
//        return $arr;
//    }
}