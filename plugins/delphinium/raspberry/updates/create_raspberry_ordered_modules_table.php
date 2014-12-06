<?php namespace Delphinium\Raspberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRaspberryOrderedModulesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_raspberry_ordered_modules') )
    	{
        	Schema::create('delphinium_raspberry_ordered_modules', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('moduleId');
                $table->integer('courseId');
                $table->integer('order');//the position this module will occupy in its parent
                $table->integer('parentId');
                $table->boolean('locked');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_raspberry_ordered_modules') )
    	{
        	Schema::drop('delphinium_raspberry_ordered_modules');
    	}
    }

}
