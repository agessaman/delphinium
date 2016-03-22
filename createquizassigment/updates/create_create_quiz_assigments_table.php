<?php namespace Delphinium\CreateQuizAssigment\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCreateQuizAssigmentsTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_createquizassigment_create_quiz_assigments', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_createquizassigment_create_quiz_assigments');
    }

}
