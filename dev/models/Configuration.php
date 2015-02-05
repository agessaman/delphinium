<?php namespace Delphinium\Dev\Models;

use Model;

class Configuration extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_dev_configuration';

    /*
     * Validation
     */
    public $rules = [
    	'Configuration_name'=>'required',
        'Token' => 'required'
    ];
}