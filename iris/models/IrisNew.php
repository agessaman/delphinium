<?php
namespace Delphinium\Iris\Models;

use Model;

/**
 * Iris-New Model
 */
class IrisNew extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_iris_iris-_news';

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