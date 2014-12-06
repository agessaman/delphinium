<?php namespace Delphinium\Blackberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBlackberryUsersTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_blackberry_users') )
    	{
        	Schema::create('delphinium_blackberry_users', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('user_id');
                $table->string('encrypted_token');
                $table->string('course_id');
                $table->timestamps();
                
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_blackberry_users') )
    	{
        	Schema::drop('delphinium_blackberry_users');
    	}
    }

}
