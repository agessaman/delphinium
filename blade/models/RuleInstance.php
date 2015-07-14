<?php namespace Delphinium\Blade\Models;

use Model;

/**
 * RuleInstance Model
 */
class RuleInstance extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_rule_instances';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    
    public $hasMany = [
        'variables' => ['Delphinium\Blade\Models\InstanceVariable']
    ];
    
    public $belongsTo = [
        'rule' => ['Delphinium\Blade\Models\Rule']
    ];
    
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}