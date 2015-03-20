<?php namespace Delphinium\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCoreAssignmentsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_core_assignments') )
    	{
        	Schema::create('delphinium_core_assignments', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->integer('assignment_id');
                $table->integer('assignment_group_id');
                $table->string('name');
                $table->string('description')->nullable;
                $table->dateTime('due_at')->nullable;
                $table->dateTime('lock_at')->nullable;
                $table->dateTime('unlock_at')->nullable;
                $table->string('all_dates')->nullable;
                $table->integer('course_id');
                $table->string('html_url');
                $table->integer('points_possible');
                $table->boolean('locked_for_user');
                $table->integer('quiz_id');
                $table->text('additional_info');//TODO: decide if we do want to implement all the other fields available in the API
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_core_assignments') )
    	{
        	Schema::drop('delphinium_core_assignments');
    	}
    }

}
