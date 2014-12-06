<?php namespace Delphinium\Raspberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRaspberryModulesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_raspberry_modules') )
    	{
        	Schema::create('delphinium_raspberry_modules', function($table)
        	{
        	
        		//this data will come from Canvas API
            	$table->engine = 'InnoDB';
                $table->increments('id');
                //we have to create our own id, because this will be coming from canvas, and we don't want it to be auto-incrementing
                $table->integer('moduleId');
                $table->integer('courseId');
                $table->string('name');
                $table->integer('position')->nullable();
                $table->dateTime('unlock_at')->nullable();
                $table->boolean('require_sequential_progress')->nullable();
                $table->boolean('publish_final_grade')->nullable();
                $table->string('prerequisite_module_ids')->nullable();
                $table->boolean('published')->nullable();
                $table->integer('items_count')->nullable();
				
				//this data is used for ordering the modules. It comes from the iris manager, not from Canvas API
                $table->integer('order');//the position this module will occupy in its parent
                $table->integer('parentId');
                $table->boolean('locked');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_raspberry_modules') )
    	{
        	Schema::drop('delphinium_raspberry_modules');
    	}
    }

}
