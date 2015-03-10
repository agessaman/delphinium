<?php namespace Delphinium\Raspberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCoreTagsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_core_tags') )
    	{
        	Schema::create('delphinium_core_tags', function($table)
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
	    if ( Schema::hasTable('delphinium_core_tags') )
    	{
        	Schema::drop('delphinium_core_tags');
    	}
    }

}
