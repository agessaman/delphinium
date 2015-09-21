<?php namespace Delphinium\Roots\Models;

use Model;

class Quiz extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_quizzes';

    protected $primaryKey = 'quiz_id';
    public $incrementing = false;//since we are using our own custom primary key (and it's not auto-incrementing)
    //we need to set this incrementing property to false
    //TODO: decide which items will be "fillable" and which ones will not
    protected $fillable = ['*'];
    
    //Validation 
    public $rules = [
    	'quiz_id'=>'required',
        'title'=>'required'
    ];


    public $hasMany = [
            'questions' => ['Delphinium\Roots\Models\Quizquestion', 
            'foreignKey' => 'question_id',
            'delete'=>'true']
    ];
    
}
