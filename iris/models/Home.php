<?php namespace Delphinium\Iris\Models;

use Model;
use October\Rain\Support\ValidationException;

class Home extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_iris_charts';

    /*
     * Validation
     */
    public $rules = [
        'Name' => 'required',
    ];


}