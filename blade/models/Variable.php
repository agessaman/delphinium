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
use \Delphinium\Blade\Classes\Rules\Variable as RulerVariable;

/**
 * Variable Model
 */
class Variable extends Model implements IRuleComponent {

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_variables';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'order', 'custom', 'default_value', 'datatype'];

    /**
     * @var array Relations
     */
    public $morphTo = [
        'parent_model' => []
    ];
    public $belongsTo = [
        'rule' => ['\Delphinium\Blade\Models\Rule'],
    ];

    public function aref_parent() {
        return $this->belongsTo('\Delphinium\Blade\Models\Variable', 'aref_parent_id');
    }

    public function aref_children() {
        return $this->hasMany('\Delphinium\Blade\Models\Variable', 'aref_parent_id');
    }

    public function operator() {
        return $this->morphOne('\Delphinium\Blade\Models\Operator', 'parent_model');
    }

    public function variable() {
        return $this->morphOne('\Delphinium\Blade\Models\Variable', 'parent_model');
    }

    public function toExecutable() {
        $op = $this->operator;

        if (isset($op)) {
            return new RulerVariable($this->name, $op->toExecutable());
        }

        $var = new RulerVariable($this->name, $this->getDefaultValue());

        $parentmodel = $this->aref_parent;
        if (isset($parentmodel)) {
            $parentmodel->toExecutable()->addArefChild($var);
        }

        return $var;
    }

    public function getDefaultValue() {
        if (!isset($this->default_value))
            return null;
        $dv = $this->default_value;
        $dt = $this->datatype;
        settype($dv, $dt);
        return $dv;
    }

    public function delete() {
        $c = $this->getChild();
        if (isset($c)) {
            $c->delete();
        }
        parent::delete();
    }

}
