<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * configuration Model
 */
class Configuration extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_configurations';

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