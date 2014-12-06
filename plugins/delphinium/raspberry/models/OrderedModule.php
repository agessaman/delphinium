<?php namespace Delphinium\Raspberry\Models;

use Model;
use October\Rain\Support\ValidationException;

class OrderedModule extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_raspberry_ordered_modules';

    /*
     * Validation
     */
     
    public $rules = [
    	'moduleId'=>'required'
    ];


}