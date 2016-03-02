<?php namespace Delphinium\Redwood\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProcessmakersTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_redwood_processmakers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('copy_name')->nullable();
            $table->integer('course_id')->unsigned();
            $table->string('process_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_redwood_processmakers');
    }

}
