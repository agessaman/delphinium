<?php namespace Delphinium\Vanilla\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBonusesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_vanilla_bonuses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('course_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_vanilla_bonuses');
    }

}
