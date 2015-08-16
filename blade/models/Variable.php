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
        'rule' => ['\Delphinium\Blade\Models\Rule']
    ];
    
    public function operator() {
        return $this->morphOne('\Delphinium\Blade\Models\Operator', 'parent_model');
    }
    
    public function variable() {
        return $this->morphOne('\Delphinium\Blade\Models\Variable', 'parent_model');
    }

    public function getChild() {
        $op = $this->operator;
        if($op) return $op;
        $var = $this->variable;
        if($var) return $var;
        return null;
    }

    public function toExecutable() {
        $child = $this->getChild();
        
        if (isset($child)) {
            return new RulerVariable($this->name, $child->toExecutable());
        }
        return new RulerVariable($this->name, $this->getDefaultValue());
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
