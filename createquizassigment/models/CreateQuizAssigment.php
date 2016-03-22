<?php namespace Delphinium\CreateQuizAssigment\Models;

use Model;

/**
 * CreateQuizAssigment Model
 */
class CreateQuizAssigment extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_createquizassigment_create_quiz_assigments';

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