<?php namespace Delphinium\Roots\Models;

use Model;

class User extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_users';

    /*
     * Validation
     */
    public $rules = [
        'user_id' => 'required'
    ];

/**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable =  array('*');
}