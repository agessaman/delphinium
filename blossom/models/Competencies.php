<?php namespace Delphinium\Blossom\Models;

use Model;
use Backend\behaviors;
use Delphinium\Blossom\FormWidgets\ColorPicker;

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
    ];//copy_id & 'course_id' => 'required' created dynamically
    
    protected $guarded = ['*'];
    protected $fillable = ['Name','Color','Animate','Size'];
    
}