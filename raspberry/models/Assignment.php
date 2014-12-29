<?php namespace Delphinium\Raspberry\Models;

use Model;
use October\Rain\Support\ValidationException;

class Assignment extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_raspberry_assignments';

    /*
     * Validation
     */
     /*
    public $rules = [
    	'Name'=>'required',
        'DeveloperId' => 'required',
        'DeveloperSecret' => 'required',
        'ConsumerKey' => 'required'
    ];
*/

}