<?php namespace Delphinium\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCoreOrderedModulesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_core_ordered_modules') )
    	{
        	Schema::create('delphinium_core_ordered_modules', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('module_id');
                $table->integer('course_id');
                $table->integer('order');//the position this module will occupy in its parent
                $table->integer('parent_id');
                $table->boolean('locked');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_core_ordered_modules') )
    	{
        	Schema::drop('delphinium_core_ordered_modules');
    	}
    }

}
