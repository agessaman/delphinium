<?php namespace Delphinium\Core\Models;

use Model;
/**
 * Description of Content
 *
 * @author damaris
 */
class Content extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_core_content';
    public $incrementing = false;
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