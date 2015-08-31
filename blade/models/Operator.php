<?php namespace Delphinium\Blade\Models;

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