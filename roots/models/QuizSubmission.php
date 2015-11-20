<?php namespace Delphinium\Roots\Models;

use Model;

class QuizSubmission extends Model
{
    public $table = 'delphinium_roots_quiz_submissions';
    protected $primaryKey = 'quiz_submission_id';
    protected $fillable = array('*');
    
}