<?php namespace Delphinium\Blossom\Models;

use Model;
use Backend\behaviors;

//use Backend\formwidgets\ColorPicker;// added for getColorOptions
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
    ];//'course_id' => 'required' created dynamically ?
    
    /* https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc */
    protected $guarded = ['*'];
    protected $fillable = ['Name', 'Color', 'Animate', 'Size'];
    
    /******* form.formRender() ********/
    public function getColorOptions() {
         // don’t know what to do yet…
    }
    /******* form.formRender() ********/
}