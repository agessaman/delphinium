<?php namespace Delphinium\Iris\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteDelphiniumIrisWalkThrough extends Migration
{
    public function up()
    {
        Schema::dropIfExists('delphinium_iris_walk_through');
    }
    
    public function down()
    {
        Schema::create('delphinium_iris_walk_through', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->integer('size');
        });
    }
}
