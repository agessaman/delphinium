<?php namespace Delphinium\Core\Models;

use Model;

class OrderedModule extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_core_ordered_modules';

    /*
     * Validation
     */
     
    public $rules = [
    	'module_id'=>'required'
    ];

    protected $fillable = array('*');
}