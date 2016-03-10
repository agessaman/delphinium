<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * Stats Model
 */
class Stats extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_stats';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

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

    public $rules = [
        'name'=>'required',
        'animate'=>'required',
        'size' => 'required'
    ];
}