<?php

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
