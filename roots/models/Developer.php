<?php namespace Delphinium\Roots\Models;

use Model;

class Developer extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_developers';

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