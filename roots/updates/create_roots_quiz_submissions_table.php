<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreteRootsQuizSubmissionsTable extends Migration
{
    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_quiz_submissions') )
    	{
            Schema::create('delphinium_roots_quiz_submissions', function($table)
            {
            	$table->engine = 'InnoDB';
                $table->integer('quiz_submission_id')->index()->unsigned();
                $table->integer('user_id');
                $table->integer('quiz_id');
                $table->integer('submission_id');
                $table->string('validation_token', 400);
                $table->integer('quiz_version')->nullable();
                $table->integer('attempt')->nullable();
                $table->integer('extra_attempts')->nullable();
                $table->integer('attempts_left');
                $table->bigInteger('time_spent')->nullable();
                $table->integer('extra_time')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('finished_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->string('workflow_state');
                $table->boolean('has_seen_results')->nullable();
                $table->boolean('manually_unlocked')->nullable();
                $table->boolean('overdue_and_needs_submission')->nullable();
                $table->integer('score'); 
                $table->integer('score_before_regrade')->nullable();
                $table->integer('quiz_points_possible')->nullable();
                $table->integer('kept_score')->nullable();
                $table->integer('fudge_points')->nullable();
                $table->string('html_url')->nullable();
                $table->timestamps();
            });
       	}
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_roots_quiz_submissions') )
    	{
        	Schema::drop('delphinium_roots_quiz_submissions');
    	}
    }

}
