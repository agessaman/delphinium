<?php namespace Delphinium\Blackberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBlackberryDevelopersTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_blackberry_developers') )
    	{
        	Schema::create('delphinium_blackberry_developers', function($table)
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
	    if ( Schema::hasTable('delphinium_blackberry_developers') )
    	{
        	Schema::drop('delphinium_blackberry_developers');
    	}
    }

}
