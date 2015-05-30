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
            $table->string('name')->unique();
            $table->string('datatype');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blade_rules');
    }

}
