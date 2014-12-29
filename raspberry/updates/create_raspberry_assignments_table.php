<?php namespace Delphinium\Raspberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRaspberryAssignmentsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_raspberry_assignments') )
    	{
        	Schema::create('delphinium_raspberry_assignments', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->bigIncrements('id');
                //we have to create our own id, because this will be coming from canvas, and we don't want it to be auto-incrementing
                $table->integer('AssignmentId');
                $table->string('Name');
                $table->string('Description');
                $table->dateTime('DueAt');
                $table->dateTime('LockAt');
                $table->dateTime('UnlockAt');
                $table->string('AllDates');
                $table->integer('CourseId');//TODO: add this relationship 
                $table->string('HtmlUrl');
                $table->integer('PointsPossible');
                $table->boolean('LockedForUser');
                $table->integer('QuizId');
                $table->text('AdditionalInfo');//TODO: decide if we do want to implement all the other fields available in the API
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_raspberry_assignments') )
    	{
        	Schema::drop('delphinium_raspberry_assignments');
    	}
    }

}
