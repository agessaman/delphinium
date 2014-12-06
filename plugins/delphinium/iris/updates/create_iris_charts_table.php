<?php namespace Delphinium\Iris\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateIrisChartsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_iris_charts') )
    	{
        	Schema::create('delphinium_iris_charts', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name')->nullable();
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_iris_charts') )
    	{
        	Schema::drop('delphinium_iris_charts');
    	}
    }

}
