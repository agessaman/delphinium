<?php namespace Delphinium\Testing\Models;

use Model;

/**
 * MyController Model
 */
class MyController extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_testing_my_controllers';

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