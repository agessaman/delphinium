<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsContentTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_content') )
    	{
        	Schema::create('delphinium_roots_content', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->bigIncrements('id');
                //we have to create our own id, because this will be coming from canvas, and we don't want it to be auto-incrementing
                $table->integer('content_id');
                $table->integer('module_item_id');
                $table->string('content_type');
                $table->string('tags');//csv
                $table->integer('points_possible');
                $table->dateTime('due_at');
                $table->dateTime('unlock_at');
                $table->string('lock_explanation');
                $table->dateTime('lock_at');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_content') )
    	{
        	Schema::drop('delphinium_roots_content');
    	}
    }

}
