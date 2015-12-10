<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsRolesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_roles') )
    	{
        	Schema::create('delphinium_roots_roles', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('role_name');
                $table->timestamps();
                
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_roles') )
    	{
        	Schema::drop('delphinium_roots_roles');
    	}
    }

}
