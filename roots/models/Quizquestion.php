<?php namespace Delphinium\Roots\Models;

use Model;

/**
 * Quizquestion Model
 */
class Quizquestion extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_roots_quiz_questions';

    protected $primaryKey = 'question_id';
    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'module' => ['Delphinium\Roots\Models\Quiz', 
        'foreignKey' => 'quiz_id',
        'delete'=>'true']
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}