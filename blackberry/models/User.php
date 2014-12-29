<?php namespace Delphinium\Blackberry\Models;

use Model;
use October\Rain\Support\ValidationException;

class User extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_blackberry_users';

    /*
     * Validation
     */
    public $rules = [
        'user_id' => 'required',
        'encrypted_token' => 'required',
        'course_id' => 'required'
    ];


}