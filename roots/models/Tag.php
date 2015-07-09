<?php namespace Delphinium\Roots\Models;

use Model;

/**
 * Description of Content
 *
 * @author Delphinium
 */
class Tag extends Model
{
//    use \October\Rain\Database\Traits\Validation;

   public $table = 'delphinium_roots_tags';
   protected $fillable = array('*');
   


}