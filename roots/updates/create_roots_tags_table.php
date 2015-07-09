<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsTagsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_tags') )
    	{
        	Schema::create('delphinium_roots_tags', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->bigIncrements('id');
                $table->integer('course_id');
                $table->string('tags');//csv
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_tags') )
    	{
        	Schema::drop('delphinium_roots_tags');
    	}
    }

}
