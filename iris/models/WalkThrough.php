<?php
namespace Delphinium\Iris\Models;

use Model;

/**
 * walkThrough Model
 */
class WalkThrough extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_iris_walk_throughs';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name','animate','size'];

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