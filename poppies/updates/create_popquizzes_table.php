<?php namespace Delphinium\Poppies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePopquizzesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_poppies_popquizzes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('quiz_name');
            $table->integer('quiz_id');
			$table->text('quiz_description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_poppies_popquizzes');
    }

}
