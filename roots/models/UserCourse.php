<?php namespace Delphinium\Roots\Models;

use Model;

/**
 * User Group Model
 */
class UserCourse extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_users_course';

    /*
     * Validation
     */
    public $rules = [
        'user_id' => 'required',
        'course_id' => 'required',
        'role' => 'required'
    ];

    
    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['Delphinium\Roots\Models\User', 'table' => 'delphinium_roots_users']
    ];

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable =  array('*');
}