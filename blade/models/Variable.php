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
    public $morphOne = [
        'variable' => ['\Delphinium\Blade\Models\Variable', 'name' => 'parent_model'],
        'operator' => ['\Delphinium\Blade\Models\Operator', 'name' => 'parent_model']
    ];
    public $belongsTo = [
        'rule' => ['\Delphinium\Blade\Models\Rule']
    ];

    public function getChild() {
        if (isset($this->variable)) return $this->variable;
        if (isset($this->operator)) return $this->operator;
        return null;
    }

    public function getTree() {
        $result = (string) $this . "\n";
        $child = $this->getChild();
        if($child != null) {
            $result .= $child->getTree();
        }
        return $result;
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
