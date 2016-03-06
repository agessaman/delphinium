<?php namespace Delphinium\BirdoParadise\Models;

use Model;

/**
 * modulemap Model
 */
class Modulemap extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_birdoparadise_modulemaps';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name','units','modules','copy_id','course_id'];

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