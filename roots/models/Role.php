<?php namespace Delphinium\Roots\Models;

use Model;

class Role extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_roles';

    /*
     * Validation
     */
    public $rules = [
        'role_name' => 'required'
    ];


}