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
    protected $fillable = ['name', 'datatype'];

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
        'actions' => ['\Delphinium\Blade\Models\Action']
    ];

    public function getTree() {
        $op = $this->operator;
        return (string) $this . "\n" . $op->getTree(1);
    }

    public function toExecutable() {
        $rb = new RuleBuilder;
        $op = $this->operator;
        $actions = [];
        
        foreach($this->actions as $action) {
            $actions[] = $action->toExecutable();
        }
        
        $rule = $rb->create($this->name, $this->datatype, $op->toExecutable(),
                $actions);
        
        $rule->exists(); // this is very inefficient, it needs future improvement
        
        return $rule;
    }

    public function getActions() {
        $arr = [];
        foreach($this->actions as $action) {
            $arr[] = $action;
        }
        usort($arr, Rule::getOrderCmp());
        return $arr;
    }
    
    public function delete() {
        foreach($this->actions as $a) {
            $a->delete();
        }
        
        $this->operator->delete();
        
        parent::delete();
    }
    
    public static function getOrderCmp() {
        return function($a, $b) {
            return $a->order - $b->order;
        };
    }
}
