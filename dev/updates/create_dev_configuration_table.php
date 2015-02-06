<?php namespace Delphinium\Dev\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDevConfigurationTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_dev_configuration') )
    	{
        	Schema::create('delphinium_dev_configuration', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Configuration_name');
                $table->integer('User_id');
                $table->integer('Course_id');
                $table->string('Token');
                $table->boolean('Enabled');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_dev_configuration') )
    	{
        	Schema::drop('delphinium_dev_configuration');
    	}
    }

}
