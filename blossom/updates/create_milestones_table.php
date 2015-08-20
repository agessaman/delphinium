<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateMilestonesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blossom_milestones', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->integer('points');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blossom_milestones');
    }

}
