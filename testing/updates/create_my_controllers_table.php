<?php namespace Delphinium\Testing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateMyControllersTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_testing_my_controllers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_testing_my_controllers');
    }

}
