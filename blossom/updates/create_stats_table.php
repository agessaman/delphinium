<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateStatsTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_stats') )
        {
            Schema::create('delphinium_blossom_stats', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->boolean('animate');
                $table->integer('size');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_stats') )
        {
            Schema::dropIfExists('delphinium_blossom_stats');
        }
    }
}
