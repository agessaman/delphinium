<?php namespace Delphinium\Blade\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateVariablesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blade_variables', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('order')->unsigned();
            $table->integer('parent_model_id')->unsigned()->index();
            $table->string('parent_model_type');
            $table->integer('rule_id')->unsigned()->index();
            $table->string('default_value')->nullable();
            $table->string('datatype');
            $table->boolean('custom')->default(false);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('delphinium_blade_variables');
    }

}
