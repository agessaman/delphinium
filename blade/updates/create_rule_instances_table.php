<?php namespace Delphinium\Blade\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRuleInstancesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blade_rule_instances', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('rule_id')->unsigned()->index();
            $table->integer('course_id')->unsigned()->index();
            $table->timestamps();
            $table->foreign('rule_id')
                    ->references('id')
                    ->on('delphinium_blade_rules');
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blade_rule_instances');
    }

}
