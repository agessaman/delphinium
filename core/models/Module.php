<?php namespace Delphinium\Core\Models;

use Model;

class Module extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_core_modules';

    protected $primaryKey = 'module_id';
    public $incrementing = false;//since we are using our own custom primary key (and it's not auto-incrementing)
    //we need to set this incrementing property to false
    //TODO: decide which items will be "fillable" and which ones will not
    protected $fillable = array('module_id','course_id','name', 'position', 'unlock_at', 'email',
        'require_sequential_progress', 'publish_final_grade', 'prerequisite_module_ids', 'items_count',
        'published','state', 'items');
    
    //Validation 
    public $rules = [
    	'module_id'=>'required'
    ];


    public $hasMany = [
            'module_items' => ['Delphinium\Core\Models\ModuleItem', 
            'foreignKey' => 'module_item_id',
            'delete'=>'true']
    ];
    
}