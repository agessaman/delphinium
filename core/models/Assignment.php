<?php namespace Delphinium\Core\Models;

use Model;

class Assignment extends Model
{
    public $incrementing = false;
    public $table = 'delphinium_core_assignments';
    protected $primaryKey = 'assignment_id';
    protected $fillable = array('assignment_id', 'assignment_group_id', 'name', 'description', 'due_at', 'unlock_at', 'all_dates', 'course_id',
        'html_url', 'points_possible', 'locked_for_user', 'quiz_id');
}