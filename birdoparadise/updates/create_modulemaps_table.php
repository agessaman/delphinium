<?php namespace Delphinium\BirdoParadise\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateModulemapsTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_birdoparadise_modulemaps', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
			$table->string('units');
            $table->string('modules');
            $table->string('course_id');
            $table->string('copy_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_birdoparadise_modulemaps');
    }

}
