<?php namespace Delphinium\Orchid\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateQuizlessonTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_orchid_quizlesson', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_orchid_quizlesson');
    }

}
