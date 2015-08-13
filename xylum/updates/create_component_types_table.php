<?php namespace Delphinium\Xylum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateComponentTypesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_xylum_component_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_xylum_component_types');
    }

}
