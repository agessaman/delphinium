<?php namespace Delphinium\Raspberry\Models;

use Model;
use October\Rain\Support\ValidationException;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Content
 *
 * @author damaris
 */
class Content extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_raspberry_content';
    protected $primaryKey = 'content_id';
    protected $fillable = array('*');//as of right now, we will only create Modules with data coming from the API, so we can make all of the attributes fillable
    /*
     * Validation
     */
     
    public $rules = [
    	'content_id'=>'required',
        'content_type' => 'required'
    ];


}