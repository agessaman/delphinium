<?php namespace Delphinium\Blade\Models;

use Model;

/**
 * RuleGroup Model
 */
class RuleGroup extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_rule_groups';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name'];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'rules' => [
            'Delphinium\Blade\Models\Rule',
            'table' => 'delphinium_blade_rule_groups_rules'
        ]
    ];
}