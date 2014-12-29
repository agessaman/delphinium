<?php namespace Delphinium\Raspberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRaspberryModuleItemsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_raspberry_module_items') )
    	{
            Schema::create('delphinium_raspberry_module_items', function($table)
            {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            //we have to create our own id, because this will be coming from canvas, and we don't want it to be auto-incrementing
            $table->integer('module_item_id');//make this the primary key
            $table->integer('course_id');
            $table->integer('module_id');
            $table->integer('position');
            $table->string('title');
            $table->integer('indent');
            $table->string('type');
            $table->integer('content_id');
            $table->string('html_url');
            $table->string('url');
            $table->string('page_url');
            $table->string('external_url');
            $table->boolean('new_tab');
            $table->string('completion_requirement');//a json representation of an array
            $table->timestamps();
            });
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_raspberry_module_items') )
    	{
        	Schema::drop('delphinium_raspberry_module_items');
    	}
    }

}
