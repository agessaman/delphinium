<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsUsersCourseTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_users_course') )
    	{
        	Schema::create('delphinium_roots_users_course', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('course_id')->unsigned();
                $table->longText('encrypted_token');
                $table->integer('role')->unsigned();
                $table->timestamps();
                
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_users_course') )
    	{
        	Schema::drop('delphinium_roots_users_course');
    	}
    }

}
