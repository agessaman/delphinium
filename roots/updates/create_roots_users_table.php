<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsUsersTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_users') )
    	{
        	Schema::create('delphinium_roots_users', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('user_id');
                $table->string('encrypted_token', 400);
                $table->string('course_id');
                $table->timestamps();
                
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_users') )
    	{
        	Schema::drop('delphinium_roots_users');
    	}
    }

}
