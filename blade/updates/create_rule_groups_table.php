<?php namespace Delphinium\Blade\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRuleGroupsTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blade_rule_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('delphinium_blade_rule_groups_rules', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('rule_group_id')->unsigned();
            $table->integer('rule_id')->unsigned();
            $table->primary(['rule_group_id', 'rule_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blade_rule_groups_rules');
        Schema::dropIfExists('delphinium_blade_rule_groups');
    }

}
