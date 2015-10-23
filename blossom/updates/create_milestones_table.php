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
            $table->integer('experience_id')->unsigned()->nullable()->index();
            $table->integer('points');
            $table->timestamps();
            $table->foreign('experience_id')->references('id')->on('delphinium_blossom_experiences');
        });
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_milestones') )
        {
            Schema::dropIfExists('delphinium_blossom_milestones');
        }
    }

}
