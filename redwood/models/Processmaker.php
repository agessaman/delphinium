<?php namespace Delphinium\Redwood\Models;

use Model;

/**
 * Processmaker Model
 */
class Processmaker extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_redwood_processmakers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

    public $rules = [
        'id'=>'required',
        'copy_name'=>'required',
        'course_id' => 'required',
        'process_id' => 'required'
    ];

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