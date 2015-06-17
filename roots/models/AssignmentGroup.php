<?php namespace Delphinium\Roots\Models;

use Model;

class AssignmentGroup extends Model
{
    public $incrementing = false;
    public $table = 'delphinium_roots_assignment_groups';
    protected $primaryKey = 'assignment_group_id';
    protected $fillable = array('*');
    
    public $hasMany = [
        'assignments' => ['Delphinium\Roots\Models\Assignment', 'foreignKey' => 'assignment_group_id']
    ];
}