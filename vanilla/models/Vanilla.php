<?php namespace Delphinium\Vanilla\Models;

use Model;

/**
 * Vanilla Model
 */
class Vanilla extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_vanilla_vanillas';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name','custom'];

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