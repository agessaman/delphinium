<?php namespace Delphinium\Blade\Models;

use Model;

/**
 * InstanceVariable Model
 */
class InstanceVariable extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_instance_variables';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    
    public $belongsTo = [
        'rule_instance' => ['Delphinium\Blade\Models\RuleInstance']
    ];
    
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}