<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * competencies Model
 */
class Competencies extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_competencies';

    public $rules = [
    	'Name'=>'required',
        'Animate'=>'required',
        'Size' => 'required'
    ];

}