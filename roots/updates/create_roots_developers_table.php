<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsDevelopersTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_developers') )
    	{
        	Schema::create('delphinium_roots_developers', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
                $table->string('DeveloperId');
                $table->string('DeveloperSecret');
                $table->string('ConsumerKey');
                $table->string('SharedSecret');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_developers') )
    	{
        	Schema::drop('delphinium_roots_developers');
    	}
    }

}
