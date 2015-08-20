<?php namespace Delphinium\Blade\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRulesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blade_rules', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('course_id')->unsigned()->nullable()->index();
            $table->string('name');
            $table->string('datatype');
            $table->timestamps();
            $table->unique(['name', 'course_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blade_rules');
    }

}
