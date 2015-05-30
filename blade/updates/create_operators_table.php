<?php namespace Delphinium\Blade\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOperatorsTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blade_operators', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('type');
            $table->integer('order')->unsigned();
            $table->integer('parent_model_id')->unsigned()->index();
            $table->string('parent_model_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blade_operators');
    }

}
