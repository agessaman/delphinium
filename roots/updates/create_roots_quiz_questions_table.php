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
                $table->integer('question_id')->unsigned();
                $table->integer('quiz_id')->unsigned()->index();
                $table->integer('position')->unsigned();
                $table->integer('points_possible')->unsigned();
                $table->string('name');
                $table->string('type');
                $table->longText('text');
                $table->longText('correct_comments');
                $table->longText('incorrect_comments');
                $table->longText('neutral_comments');
                $table->longText('answers');
                $table->timestamps();
                

                $table->foreign('quiz_id')->references('quiz_id')->on('delphinium_roots_quizzes');

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
