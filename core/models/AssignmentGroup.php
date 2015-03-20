<?php namespace Delphinium\Core\Models;

use Model;

class AssignmentGroup extends Model
{
    public $incrementing = false;
    public $table = 'delphinium_core_assignment_groups';
    protected $primaryKey = 'assignment_group_id';
    protected $fillable = array('*');
    
    public $hasMany = [
        'assignments' => ['Delphinium\Core\Models\Assignment', 'foreignKey' => 'assignment_group_id']
    ];
}