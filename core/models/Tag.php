<?php namespace Delphinium\Core\Models;

use Model;

/**
 * Description of Content
 *
 * @author damaris
 */
class Tag extends Model
{
//    use \October\Rain\Database\Traits\Validation;

   public $table = 'delphinium_core_tags';
   protected $fillable = array('*');
   


}