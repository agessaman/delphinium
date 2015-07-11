<?php

namespace Delphinium\Blade\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateFilterActionsTable extends Migration {

    public function up() {
        Schema::create('delphinium_blade_filter_actions', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->boolean('excluded');
            $table->integer('rule_id')->unsigned()->index();
            $table->integer('order')->unsigned();
            $table->timestamps();
            $table->foreign('rule_id')->references('id')->on('delphinium_blade_rules');
        });
    }

    public function down() {
        Schema::dropIfExists('delphinium_blade_filter_actions');
    }

}
