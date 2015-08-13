<?php namespace Delphinium\Xylum\Models;

use Model;

/**
 * ComponentRules Model
 */
class ComponentRules extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_xylum_component_rules';

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
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}