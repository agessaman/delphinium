<?php namespace Delphinium\Core\Models;

use Model;

class Assignment extends Model
{
    public $incrementing = false;
    public $table = 'delphinium_core_assignments';
    protected $primaryKey = 'assignment_id';
    protected $fillable = array('*');
    
    public $belongsTo = [
        'assignment_group' => ['Delphinium\Core\Models\AssignmentGroup', 'foreignKey' => 'assignment_group_id']
    ];
}