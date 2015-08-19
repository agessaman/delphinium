<?php namespace Delphinium\Xylum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateComponentInstancesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_xylum_component_instances', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('type');
            $table->text('data');
            $table->integer('course_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_xylum_component_instances');
    }

}

