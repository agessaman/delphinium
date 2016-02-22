<?php namespace Delphinium\Vanilla\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBoardsTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_vanilla_boards', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_vanilla_boards');
    }

}
