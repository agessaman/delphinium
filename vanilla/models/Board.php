<?php namespace Delphinium\Vanilla\Models;

use Model;

/**
 * board Model
 */
class Board extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_vanilla_boards';

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