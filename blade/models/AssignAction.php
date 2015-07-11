<?php namespace Delphinium\Blade\Models;

use Model;
use \Delphinium\Blade\Classes\Rules\Variable as RulerVariable;
use \Delphinium\Blade\Classes\Rules\Action\AssignAction as AAction;

/**
 * Action Model
 */
class AssignAction extends Model implements IRuleComponent
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_assign_actions';

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'variable_name'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'rule' => 
        [
            'Delphinium\Blade\Models\Rule'
        ]
    ];
    
    public $morphOne = [
        'variable' => ['Delphinium\Blade\Models\Variable', 'name' => 'parent_model']
    ];
    
    public function getChild() {
        return $this->variable;
    }

    public function toExecutable() {
        return new AAction(new RulerVariable($this->variable_name), $this->variable->toExecutable());
    }

}