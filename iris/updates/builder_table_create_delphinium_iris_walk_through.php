<?php namespace Delphinium\Iris\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDelphiniumIrisWalkThrough extends Migration
{
    public function up()
    {
        Schema::create('delphinium_iris_walk_through', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 255);
            $table->integer('size');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('delphinium_iris_walk_through');
    }
}
