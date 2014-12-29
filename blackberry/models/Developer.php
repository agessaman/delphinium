<?php namespace Delphinium\Blackberry\Models;

use Model;
use October\Rain\Support\ValidationException;

class Developer extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_blackberry_developers';

    /*
     * Validation
     */
    public $rules = [
    	'Name'=>'required',
        'DeveloperId' => 'required',
        'DeveloperSecret' => 'required',
        'ConsumerKey' => 'required'
    ];


}