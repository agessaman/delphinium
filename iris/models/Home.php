<?php namespace Delphinium\Iris\Models;

use Model;
use Delphinium\Roots\Classes\CustomModel;
use October\Rain\Support\ValidationException;

class Home extends CustomModel
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