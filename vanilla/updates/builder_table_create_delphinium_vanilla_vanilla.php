<?php namespace Delphinium\Vanilla\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDelphiniumVanillaVanilla extends Migration
{
    public function up()
    {
        Schema::create('delphinium_vanilla_vanilla', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->string('course_id', 255);
            $table->string('copy_id', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('delphinium_vanilla_vanilla');
    }
}
