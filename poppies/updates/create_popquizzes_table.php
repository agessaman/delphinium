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
            $table->string('name',255);
            $table->string('quiz_name',255);//title
			$table->string('quiz_description',512);//long
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_poppies_popquizzes');
    }

}
