<?php namespace Delphinium\Blossom\Models;

use Model;
use Backend\behaviors;
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
		'Color'=>'required',
        'Animate'=>'required',
        'Size' => 'required'
    ];
	/*
	public function getAnimateOptions($value)
	{
		
	}
	*/
}