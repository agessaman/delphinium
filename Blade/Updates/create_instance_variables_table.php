<?php namespace Delphinium\Blade\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateInstanceVariablesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blade_instance_variables', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('rule_instance_id')->unsigned()->index();
            $table->string('name')->unique();
            $table->string('value');
            $table->string('datatype');
            $table->timestamps();
            $table->foreign('rule_instance_id')
                    ->references('id')
                    ->on('delphinium_blade_rule_instances');
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blade_instance_variables');
    }

}
