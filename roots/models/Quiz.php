<?php namespace Delphinium\Roots\Models;

use Model;

class Quiz extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_quizzes';

    protected $primaryKey = 'id';
    public $incrementing = false;//since we are using our own custom primary key (and it's not auto-incrementing)
    //we need to set this incrementing property to false
    //TODO: decide which items will be "fillable" and which ones will not
    protected $fillable = ['*'];
    
    //Validation 
    public $rules = [
    	'id'=>'required',
        'title'=>'required'
    ];


//    public $hasMany = [
//            'module_items' => ['Delphinium\Roots\Models\ModuleItem', 
//            'foreignKey' => 'module_item_id',
//            'delete'=>'true']
//    ];
    
}
