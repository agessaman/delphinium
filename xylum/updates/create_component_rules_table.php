<?php namespace Delphinium\Xylum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateComponentRulesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_xylum_component_rules', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('component_id');
            $table->integer('rule_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_xylum_component_rules');
    }

}
