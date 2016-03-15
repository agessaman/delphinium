<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * EasterEggs Model
 */
class EasterEggs extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_easter_eggs';

    public $rules = [
        'Name'=>'required'
    ];//copy_id & 'course_id' => 'required' created dynamically
    
    

}