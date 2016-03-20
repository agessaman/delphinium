<?php namespace Delphinium\Vanilla\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateVanillasTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_vanilla_vanillas', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
			$table->string('custom',255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_vanilla_vanillas');
    }

}
