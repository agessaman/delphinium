<?php namespace Delphinium\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCoreAssignmentGroupsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_core_assignment_groups') )
    	{
        	Schema::create('delphinium_core_assignment_groups', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->integer('assignment_group_id');
                $table->string('name');
                $table->integer('position');
                $table->integer('group_weight');
                $table->text('rules');
                $table->integer('course_id');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_core_assignment_groups') )
    	{
        	Schema::drop('delphinium_core_assignment_groups');
    	}
    }

}
