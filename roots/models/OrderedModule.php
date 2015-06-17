<?php namespace Delphinium\Roots\Models;

use Model;

class OrderedModule extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_ordered_modules';

    /*
     * Validation
     */
     
    public $rules = [
    	'module_id'=>'required'
    ];

    protected $fillable = array('*');
}