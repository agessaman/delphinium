<?php namespace Delphinium\Roots\Models;

use Model;

class Assignment extends Model
{
    public $incrementing = false;
    public $table = 'delphinium_roots_assignments';
    protected $primaryKey = 'assignment_id';
    protected $fillable = array('*');
    
    public $belongsTo = [
        'assignment_group' => ['Delphinium\Roots\Models\AssignmentGroup', 'foreignKey' => 'assignment_group_id']
    ];
}