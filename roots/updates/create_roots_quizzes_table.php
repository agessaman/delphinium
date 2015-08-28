<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsQuizzesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_quizzes') )
    	{
            Schema::create('delphinium_roots_quizzes', function($table)
            {
                $table->engine = 'InnoDB';
                $table->integer('id');
                $table->integer('course_id');
                $table->string('title');
                $table->string('description');
                $table->string('html_url');
                $table->string('quiz_type');
                $table->integer('assignment_group_id');
                $table->integer('time_limit');
                $table->integer('question_count');
                $table->integer('points_possible');
                $table->dateTime('due_at');
                $table->dateTime('lock_at');
                $table->dateTime('unlock_at');
                $table->boolean('published');
                $table->boolean('locked_for_user');
                $table->string('scoring_policy');
                $table->integer('allowed_attempts');
                $table->timestamps();
            });
       	 }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_roots_quizzes') )
    	{
            Schema::drop('delphinium_roots_quizzes');
    	}
    }

}
