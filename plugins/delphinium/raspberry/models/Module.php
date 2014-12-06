<?php namespace Delphinium\Raspberry\Models;

use Model;
use October\Rain\Support\ValidationException;
use Delphinium\Raspberry\Models\ModuleItem;

class Module extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_raspberry_modules';

    protected $primaryKey = 'moduleId';
    protected $fillable = array('*');//as of right now, we will only create Modules with data coming from the API, so we can make all of the attributes fillable
    /*
     * Validation
     */
     
    public $rules = [
    	'moduleId'=>'required',
        'name' => 'required'
    ];


    public $hasMany = [
        'moduleItems' => ['Delphinium\Raspberry\Models\ModuleItem', 'foreignKey' => 'module_item_id']
    ];
    
}