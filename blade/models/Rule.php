<?php

namespace Delphinium\Blade\Models;

use Model;
use \Delphinium\Blade\Classes\Rules\RuleBuilder;

/**
 * Rule Model
 */
class Rule extends Model implements IRuleComponent {

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_rules';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'datatype', 'course_id'];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'rule_groups' => [
            '\Delphinium\Blade\Models\RuleGroup',
            'table' => 'delphinium_blade_rule_groups_rules'
        ]
    ];
    
    public $morphOne = [
        'operator' => [
            '\Delphinium\Blade\Models\Operator',
            'name' => 'parent_model',
        ]
    ];
    
    public $hasMany = [
        'assign_actions' => ['\Delphinium\Blade\Models\AssignAction'],
        'filters' => ['\Delphinium\Blade\Models\FilterAction'],
        'variables' => ['\Delphinium\Blade\Models\Variable']
    ];

    public function getTree() {
        $op = $this->operator;
        return (string) $this . "\n" . $op->getTree(1);
    }

    public function toExecutable() {
        $rb = new RuleBuilder;
        $op = $this->operator;
        
        $actions = [];
        
        foreach ($this->getActions() as $action) {
            $actions[] = $action->toExecutable();
        }
        
        $rule = $rb->create($this->name, $this->datatype, $op->toExecutable(),
                $actions);
        
        $rule->exists(); // this is very inefficient, it needs future improvement
        
        return $rule;
    }

    public function getActions() {
        $arr = [];
        
        foreach($this->assign_actions as $action) {
            $arr[] = $action;
        }
        
        foreach($this->filters as $filter) {
            $arr[] = $filter;
        }
        
        usort($arr, Rule::getOrderCmp());
        return $arr;
    }
    
    public function delete() {
        foreach($this->getActions() as $a) {
            $a->delete();
        }
        
        $this->operator->delete();
        
        parent::delete();
    }
    
    /**
     * Returns an array of variable names
     */
    public function getKeys() {
        $vars = $this->variables;
        return array_map(function ($var) {return $var->name;}, $vars);
    }
    
    /**
     * 
     * @param type $name the name of the variable to get the value for
     */
    public function getVariableDefaultValue($name) {
        $var = $this->variables()->where('name', '=', $name)->first();
        $value = $var->default_value;
        
        if(!isset($value)) return null;
        
        settype($value, $var->datatype);
        return $value;
    }
    
    public static function getOrderCmp() {
        return function($a, $b) {
            return $a->order - $b->order;
        };
    }
}
