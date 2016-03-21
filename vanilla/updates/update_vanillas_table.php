<?php namespace Delphinium\Vanilla\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateVanillasTable extends Migration
{

    public function up()
    {
		Schema::dropIfExists('delphinium_vanilla_vanillas');
        Schema::create('delphinium_vanilla_vanillas', function($table)
        {
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->string('name',255);
			$table->string('custom',255);
			$table->string('course_id',255);
			$table->string('copy_id',255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_vanilla_vanillas');
		// drop just course and id ???
    }

}
