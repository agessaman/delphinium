<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsQuizQuestionsTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_roots_quiz_questions') )
    	{
            Schema::create('delphinium_roots_quiz_questions', function($table)
            {
                $table->engine = 'InnoDB';
                $table->integer('quiz_id')->unsigned()->index();
                $table->integer('position');
                $table->integer('points_possible');
                $table->string('name');
                $table->string('type');
                $table->string('text');
                $table->string('correct_comments');
                $table->string('incorrect_comments');
                $table->string('neutral_comments');
                $table->longText('answers');
                $table->timestamps();
                $table->foreign('quiz_id')->references('id')->on('delphinium_roots_quizzes');

            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_roots_quiz_questions') )
    	{
            Schema::dropIfExists('delphinium_roots_quiz_questions');
        }
    }

}
